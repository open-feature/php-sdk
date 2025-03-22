<?php

declare(strict_types=1);

namespace OpenFeature;

use League\Event\EventDispatcher;
use OpenFeature\implementation\events\Event;
use OpenFeature\implementation\flags\NoOpClient;
use OpenFeature\implementation\provider\ProviderAwareTrait;
use OpenFeature\interfaces\common\LoggerAwareTrait;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\events\EventDetails;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\flags\API;
use OpenFeature\interfaces\flags\Client;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ProviderAware;
use Psr\Log\LoggerAwareInterface;
use Throwable;

use function is_null;
use function key_exists;

final class OpenFeatureAPI implements API, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * -----------------
     * Requirement 1.1.2
     * -----------------
     * The API MUST provide a function to set the global provider singleton, which
     * accepts an API-conformant provider implementation.
     */
    use ProviderAwareTrait;

    private static ?OpenFeatureAPI $instance = null;

    private EventDispatcher $dispatcher;

    /** @var (Client & ProviderAware) | null $defaultClient */
    private $defaultClient;
    /** @var array<string,Client & ProviderAware> $clientMap */
    private array $clientMap = [];

    /** @var Hook[] $hooks */
    private array $hooks = [];

    private ?EvaluationContext $evaluationContext = null;

    /**
     * -----------------
     * Requirement 1.1.1
     * -----------------
     * The API, and any state it maintains SHOULD exist as a global singleton, even
     * in cases wherein multiple versions of the API are present at runtime.
     */
    public static function getInstance(): API
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
     */
    private function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    /**
     * -----------------
     * Requirement 1.1.3
     * -----------------
     * The API MUST provide a function to bind a given provider to one or more client names.
     * If the client-name already has a bound provider, it is overwritten with the new mapping.
     */
    public function setClientProvider(string $clientDomain, Provider $provider): void
    {
        if (!isset($this->clientMap[$clientDomain])) {
            return;
        }

        $this->clientMap[$clientDomain]->setProvider($provider);
    }

    /**
     * -----------------
     * Requirement 1.1.5
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
     * Requirement 1.1.6
     * -----------------
     * The API MUST provide a function for creating a client which accepts the following options:
     *   domain (optional): A logical string identifier for the client.
     */
    public function getClient(?string $domain = null): Client
    {
        try {
            $isDefaultClient = is_null($domain);
            $domain = $domain ?? self::class;

            if ($isDefaultClient) {
                $currentClient = $this->defaultClient ?? null;
            } else {
                $currentClient = key_exists($domain, $this->clientMap) ? $this->clientMap[$domain] : null;
            }

            if (!is_null($currentClient)) {
                return $currentClient;
            }

            try {
                $client = new OpenFeatureClient($this, $domain);
                $client->setLogger($this->getLogger());
            } catch (Throwable $err) {
                $client = new NoOpClient();
            }

            if ($isDefaultClient) {
                $this->defaultClient = $client;
            } else {
                $this->clientMap[$domain] = $client;
            }

            return $client;
        } catch (Throwable $err) {
            /**
             * -----------------
             * Requirement 1.1.7
             * -----------------
             * The client creation function MUST NOT throw, or otherwise abnormally terminate.
             */
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
     * Requirement 1.1.4
     * -----------------
     * The API MUST provide a function to add hooks which accepts one or more API-conformant
     * hooks, and appends them to the collection of any previously added hooks. When new
     * hooks are added, previously added hooks are not removed.
     */
    public function addHooks(Hook ...$hooks): void
    {
        $this->hooks = [...$this->hooks, ...$hooks];
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

    /**
     * -----------------
     * Requirement 1.6.1
     * -----------------
     * The API MUST define a shutdown function which, when called, must call the respective
     * shutdown function on the active provider.
     */
    public function dispose(): void
    {
        $this->getProvider()->dispose();
    }

    /**
     * -----------------
     * Requirement 5.1.1
     * -----------------
     * The provider MAY define a mechanism for signaling the occurrence of one of a set
     * of events, including PROVIDER_READY, PROVIDER_ERROR, PROVIDER_CONFIGURATION_CHANGED
     * and PROVIDER_STALE, with a provider event details payload.
     */
    public function dispatch(ProviderEvent $providerEvent, EventDetails $eventDetails): void
    {
        $this->dispatcher->dispatch(new Event($providerEvent->value, $eventDetails));
    }

    public function addHandler(ProviderEvent $providerEvent, callable $handler): void
    {
        $this->dispatcher->subscribeTo($providerEvent->value, $handler);
    }

    public function removeHandler(ProviderEvent $providerEvent, callable $handler): void
    {
    }

    /**
     * TESTING UTILITY
     */
    protected function resetClients(): void
    {
        $this->defaultClient = null;
        $this->clientMap = [];
    }

    /**
     * TESTING UTILITY
     */
    protected function resetProviders(): void
    {
        $this->provider = null;
    }
}
