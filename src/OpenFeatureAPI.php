<?php

declare(strict_types=1);

namespace OpenFeature;

use Closure;
use LogicException;
use OpenFeature\implementation\events\EventDetails;
use OpenFeature\implementation\events\ProviderEventDetails as ProviderEventDetailsImplementation;
use OpenFeature\implementation\flags\EvaluationContext as EvaluationContextImplementation;
use OpenFeature\implementation\flags\NoOpClient;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\common\LoggerAwareTrait;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\events\EventDetails as EventDetailsInterface;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderEventDetails;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\EventAwareClient;
use OpenFeature\interfaces\flags\ProviderLifecycleAPI;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ProviderEventAware;
use OpenFeature\interfaces\provider\ProviderEventEmitter;
use OpenFeature\interfaces\provider\ProviderLifecycle;
use OpenFeature\interfaces\provider\ThrowableWithResolutionError;
use Psr\Log\LoggerAwareInterface;
use Throwable;
use WeakReference;

use function array_merge;
use function is_null;

final class OpenFeatureAPI implements LoggerAwareInterface, ProviderLifecycleAPI
{
    use LoggerAwareTrait;

    private static ?OpenFeatureAPI $instance = null;

    private Provider $provider;

    /** @var Hook[] $hooks */
    private array $hooks = [];
    private ?EvaluationContext $evaluationContext = null;
    private ProviderStatus $providerStatus;

    /** @var Closure(ProviderEvent, ProviderEventDetails): void|null */
    private ?Closure $providerEventHandler = null;

    /** @var array<string, array<int, callable>> */
    private array $eventHandlers = [];

    /** @var array<int, WeakReference<OpenFeatureClient>> */
    private array $clients = [];

    /** @var array<string, EventDetailsInterface> */
    private array $lastEventDetails = [];

    /**
     * -----------------
     * Requirement 1.1.1
     * -----------------
     * The API, and any state it maintains SHOULD exist as a global singleton, even
     * in cases wherein multiple versions of the API are present at runtime.
     */
    public static function getInstance(): ProviderLifecycleAPI
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Requirement 1.1.1
     *
     * The API, and any state it maintains SHOULD exist as a global singleton, even in cases wherein multiple versions of the API are present at runtime.
     *
     * It's important that multiple instances of the API not be active, so that state stored therein, such as the registered provider, static global
     * evaluation context, and globally configured hooks allow the API to behave predictably. This can be difficult in some runtimes or languages, but
     * implementors should make their best effort to ensure that only a single instance of the API is used.
     *
     * For isolated instances, prefer using the factory function in OpenFeature\isolated.
     *
     * @see \OpenFeature\isolated\OpenFeatureAPIFactory::createAPI()
     */
    public function __construct()
    {
        $this->provider = new NoOpProvider();
        $this->providerStatus = ProviderStatus::READY();
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    protected function resolveClient(string $name, string $version): OpenFeatureClient
    {
        return new OpenFeatureClient($this, $name, $version);
    }

    /** @internal Registers a client for provider event delivery. */
    public function registerClient(OpenFeatureClient $client): void
    {
        $this->clients[] = WeakReference::create($client);
    }

    /**
     * -----------------
     * Requirement 1.1.2
     * -----------------
     * The API MUST provide a function to set the global provider singleton, which
     * accepts an API-conformant provider implementation.
     */
    public function setProvider(Provider $provider): void
    {
        $this->setProviderAndWait($provider);
    }

    public function setProviderAndWait(Provider $provider): void
    {
        if ($provider === $this->provider) {
            if ($this->providerStatus->equals(ProviderStatus::READY())) {
                return;
            }

            $this->providerStatus = ProviderStatus::NOT_READY();
            $this->lastEventDetails = [];
            $this->initializeProvider($provider);

            return;
        }

        $previousProvider = $this->provider;
        $previousHandler = $this->providerEventHandler;

        $this->provider = $provider;
        $this->providerStatus = ProviderStatus::NOT_READY();
        $this->lastEventDetails = [];
        $this->providerEventHandler = null;
        $this->subscribeToProvider($provider);

        try {
            $this->initializeProvider($provider);
        } finally {
            $this->unsubscribeFromProvider($previousProvider, $previousHandler);
            $this->shutdownProvider($previousProvider);
        }
    }

    private function initializeProvider(Provider $provider): void
    {
        if (!$provider instanceof ProviderLifecycle) {
            $this->processProviderEvent(ProviderEvent::READY(), new ProviderEventDetailsImplementation());

            return;
        }

        $context = $this->evaluationContext ?? new EvaluationContextImplementation();

        try {
            $provider->initialize($context, null);
        } catch (Throwable $error) {
            if (!$provider instanceof ProviderEventAware) {
                $errorCode = $error instanceof ThrowableWithResolutionError
                    ? $error->getResolutionError()->getResolutionErrorCode()
                    : ErrorCode::GENERAL();
                $this->processProviderEvent(
                    ProviderEvent::ERROR(),
                    new ProviderEventDetailsImplementation($error->getMessage(), [], [], $errorCode),
                );
            } elseif (
                !$this->providerStatus->equals(ProviderStatus::ERROR())
                && !$this->providerStatus->equals(ProviderStatus::FATAL())
            ) {
                throw new LogicException(
                    'An event-aware provider must emit PROVIDER_ERROR before initialization terminates abnormally.',
                    0,
                    $error,
                );
            }

            throw $error;
        }

        if (!$provider instanceof ProviderEventAware) {
            $this->processProviderEvent(ProviderEvent::READY(), new ProviderEventDetailsImplementation());

            return;
        }

        if (
            $this->providerStatus->equals(ProviderStatus::ERROR())
            || $this->providerStatus->equals(ProviderStatus::FATAL())
        ) {
            $eventDetails = $this->lastEventDetails[ProviderEvent::ERROR()->getValue()] ?? null;
            $errorCode = $eventDetails === null ? null : $eventDetails->getErrorCode();
            $errorMessage = $eventDetails === null ? null : $eventDetails->getMessage();

            throw new ResolutionError(
                $errorCode ?? ErrorCode::GENERAL(),
                $errorMessage ?? 'Provider initialization failed.',
            );
        }

        if (!$this->providerStatus->equals(ProviderStatus::READY())) {
            throw new LogicException('An event-aware provider must emit PROVIDER_READY or PROVIDER_ERROR during initialization.');
        }
    }

    private function subscribeToProvider(Provider $provider): void
    {
        if (!$provider instanceof ProviderEventEmitter) {
            return;
        }

        $handler = function (ProviderEvent $event, ProviderEventDetails $details) use ($provider): void {
            if ($provider !== $this->provider) {
                return;
            }

            $this->processProviderEvent($event, $details);
        };
        $this->providerEventHandler = $handler;
        $provider->addProviderEventHandler($handler);
    }

    private function processProviderEvent(ProviderEvent $event, ProviderEventDetails $details): void
    {
        if ($event->equals(ProviderEvent::READY())) {
            $this->providerStatus = ProviderStatus::READY();
        } elseif ($event->equals(ProviderEvent::STALE())) {
            $this->providerStatus = ProviderStatus::STALE();
        } elseif ($event->equals(ProviderEvent::ERROR())) {
            $errorCode = $details->getErrorCode();
            $this->providerStatus = $errorCode !== null && $errorCode->equals(ErrorCode::PROVIDER_FATAL())
                ? ProviderStatus::FATAL()
                : ProviderStatus::ERROR();
        }

        $eventDetails = new EventDetails($this->provider->getMetadata()->getName(), $details);
        $this->lastEventDetails[$event->getValue()] = $eventDetails;

        /** @var array<int, array{OpenFeatureClient, array<int, callable>}> $clientHandlers */
        $clientHandlers = [];

        foreach ($this->clients as $index => $clientReference) {
            $client = $clientReference->get();
            if (!$client instanceof OpenFeatureClient) {
                unset($this->clients[$index]);

                continue;
            }

            $clientHandlers[] = [$client, $client->getProviderEventHandlers($event)];
        }

        $this->runHandlers($this->eventHandlers[$event->getValue()] ?? [], $eventDetails);

        foreach ($clientHandlers as [$client, $handlers]) {
            $client->handleProviderEvent($eventDetails, $handlers);
        }
    }

    /** @param array<int, callable> $handlers */
    private function runHandlers(array $handlers, EventDetailsInterface $details): void
    {
        foreach ($handlers as $handler) {
            try {
                $handler($details);
            } catch (Throwable $error) {
                try {
                    $this->getLogger()->error('OpenFeature provider event handler failed.', ['exception' => $error]);
                } catch (Throwable) {
                    // Event handlers remain isolated even if the configured logger fails.
                }
            }
        }
    }

    /** @param callable(EventDetailsInterface): void $handler */
    public function addHandler(ProviderEvent $event, callable $handler): void
    {
        $this->eventHandlers[$event->getValue()][] = $handler;

        if ($this->statusMatchesEvent($event)) {
            $details = $this->lastEventDetails[$event->getValue()]
                ?? new EventDetails($this->provider->getMetadata()->getName());
            $this->runHandlers([$handler], $details);
        }
    }

    /** @param callable(EventDetailsInterface): void $handler */
    public function removeHandler(ProviderEvent $event, callable $handler): void
    {
        foreach ($this->eventHandlers[$event->getValue()] ?? [] as $index => $registeredHandler) {
            if ($registeredHandler === $handler) {
                unset($this->eventHandlers[$event->getValue()][$index]);
            }
        }
    }

    private function statusMatchesEvent(ProviderEvent $event): bool
    {
        return ($event->equals(ProviderEvent::READY()) && $this->providerStatus->equals(ProviderStatus::READY()))
            || ($event->equals(ProviderEvent::STALE()) && $this->providerStatus->equals(ProviderStatus::STALE()))
            || ($event->equals(ProviderEvent::ERROR())
                && ($this->providerStatus->equals(ProviderStatus::ERROR())
                    || $this->providerStatus->equals(ProviderStatus::FATAL())));
    }

    /** @internal Used to supply the latest state details to newly registered client handlers. */
    public function getLastEventDetails(ProviderEvent $event): ?EventDetailsInterface
    {
        return $this->lastEventDetails[$event->getValue()] ?? null;
    }

    /** @param Closure(ProviderEvent, ProviderEventDetails): void|null $handler */
    private function unsubscribeFromProvider(Provider $provider, ?Closure $handler): void
    {
        if ($provider instanceof ProviderEventEmitter && $handler !== null) {
            $provider->removeProviderEventHandler($handler);
        }
    }

    private function shutdownProvider(Provider $provider): void
    {
        if (!$provider instanceof ProviderLifecycle) {
            return;
        }

        try {
            $provider->shutdown();
        } catch (Throwable $error) {
            try {
                $this->getLogger()->error('OpenFeature provider shutdown failed.', ['exception' => $error]);
            } catch (Throwable) {
                // Provider replacement and API shutdown must remain safe if logging fails.
            }
        }
    }

    public function getProviderStatus(): ProviderStatus
    {
        return $this->providerStatus;
    }

    public function shutdown(): void
    {
        $provider = $this->provider;
        $handler = $this->providerEventHandler;

        $this->unsubscribeFromProvider($provider, $handler);
        $this->provider = new NoOpProvider();
        $this->providerStatus = ProviderStatus::READY();
        $this->providerEventHandler = null;
        $this->evaluationContext = null;
        $this->hooks = [];
        $this->eventHandlers = [];
        $this->lastEventDetails = [];
        $this->clients = [];
        $this->logger = null;

        $this->shutdownProvider($provider);
    }

    /**
     * -----------------
     * Requirement 1.1.4
     * -----------------
     * The API MUST provide a function for retrieving the metadata field of the
     * configured provider.
     */
    public function getProviderMetadata(): Metadata
    {
        return $this->getProvider()->getMetadata();
    }

    /**
     * -----------------
     * Requirement 1.1.4
     * -----------------
     * The API MUST provide a function for creating a client which accepts the following options:
     *   name (optional): A logical string identifier for the client.
     */
    public function getClient(?string $name = null, ?string $version = null): EventAwareClient
    {
        $name = $name ?? 'OpenFeature';
        $version = $version ?? 'OpenFeature';

        try {
            $client = $this->resolveClient($name, $version);
            $client->setLogger($this->getLogger());

            return $client;
        } catch (Throwable $err) {
            return new NoOpClient();
        }
    }

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * -----------------
     * Requirement 1.1.3
     * -----------------
     * The API MUST provide a function to add hooks which accepts one or more API-conformant
     * hooks, and appends them to the collection of any previously added hooks. When new
     * hooks are added, previously added hooks are not removed.
     */
    public function addHooks(Hook ...$hooks): void
    {
        $this->hooks = array_merge($this->hooks, $hooks);
    }

    public function clearHooks(): void
    {
        $this->hooks = [];
    }

    public function getEvaluationContext(): ?EvaluationContext
    {
        return $this->evaluationContext;
    }

    public function setEvaluationContext(EvaluationContext $context): void
    {
        $this->evaluationContext = $context;
    }
}
