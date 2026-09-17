<?php

declare(strict_types=1);

namespace OpenFeature;

use DateTime;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\common\ValueTypeValidator;
use OpenFeature\implementation\errors\FlagValueTypeError;
use OpenFeature\implementation\errors\InvalidResolutionValueError;
use OpenFeature\implementation\events\EventDetails;
use OpenFeature\implementation\flags\EvaluationContext;
use OpenFeature\implementation\flags\EvaluationDetailsBuilder;
use OpenFeature\implementation\flags\EvaluationDetailsFactory;
use OpenFeature\implementation\flags\EvaluationOptions;
use OpenFeature\implementation\hooks\HookContextBuilder;
use OpenFeature\implementation\hooks\HookContextFactory;
use OpenFeature\implementation\hooks\HookExecutor;
use OpenFeature\implementation\hooks\HookHints;
use OpenFeature\implementation\provider\Reason;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\common\LoggerAwareTrait;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\events\EventDetails as EventDetailsInterface;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\events\ProviderStatusAccessor;
use OpenFeature\interfaces\flags\API;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;
use OpenFeature\interfaces\flags\EvaluationDetails as EvaluationDetailsInterface;
use OpenFeature\interfaces\flags\EvaluationOptions as EvaluationOptionsInterface;
use OpenFeature\interfaces\flags\EventAwareClient;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HooksAwareTrait;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use OpenFeature\interfaces\provider\ThrowableWithResolutionError;
use Psr\Log\LoggerAwareInterface;
use Throwable;

use function array_merge;
use function array_reverse;

class OpenFeatureClient implements EventAwareClient, LoggerAwareInterface
{
    use HooksAwareTrait;
    use LoggerAwareTrait;

    private API $api;
    private string $name;
    private string $version;
    private ?EvaluationContextInterface $evaluationContext = null;

    /** @var array<string, array<int, callable>> */
    private array $eventHandlers = [];

    /**
     * Client for evaluating the flag. There may be multiples of these floating around.
     *
     * @param API $api Backing global singleton
     * @param string $name Name of the client (used by observability tools).
     * @param string $version Version of the client (used by observability tools).
     */
    public function __construct(API $api, string $name, string $version)
    {
        $this->api = $api;
        $this->name = $name;
        $this->version = $version;
        $this->hooks = [];

        if ($api instanceof OpenFeatureAPI) {
            $api->registerClient($this);
        }
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Return an optional client-level evaluation context.
     */
    public function getEvaluationContext(): ?EvaluationContextInterface
    {
        return $this->evaluationContext;
    }

    /**
     * Set the client-level evaluation context.
     *
     * @param EvaluationContextInterface $context Client level context.
     */
    public function setEvaluationContext(EvaluationContextInterface $context): void
    {
        $this->evaluationContext = $context;
    }

    public function getProviderStatus(): ProviderStatus
    {
        return $this->api instanceof ProviderStatusAccessor
            ? $this->api->getProviderStatus()
            : ProviderStatus::READY();
    }

    /** @param callable(EventDetailsInterface): void $handler */
    public function addHandler(ProviderEvent $event, callable $handler): void
    {
        $this->eventHandlers[$event->getValue()][] = $handler;

        if (!$this->statusMatchesEvent($event)) {
            return;
        }

        $details = $this->api instanceof OpenFeatureAPI
            ? $this->api->getLastEventDetails($event)
            : null;
        $this->runEventHandlers(
            [$handler],
            $details ?? new EventDetails($this->api->getProviderMetadata()->getName()),
        );
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
        $status = $this->getProviderStatus();

        return ($event->equals(ProviderEvent::READY()) && $status->equals(ProviderStatus::READY()))
            || ($event->equals(ProviderEvent::STALE()) && $status->equals(ProviderStatus::STALE()))
            || ($event->equals(ProviderEvent::ERROR())
                && ($status->equals(ProviderStatus::ERROR()) || $status->equals(ProviderStatus::FATAL())));
    }

    /** @internal Called by the API when the currently bound provider emits an event. */
    public function handleProviderEvent(ProviderEvent $event, EventDetailsInterface $details): void
    {
        $this->runEventHandlers($this->eventHandlers[$event->getValue()] ?? [], $details);
    }

    /** @param array<int, callable> $handlers */
    private function runEventHandlers(array $handlers, EventDetailsInterface $details): void
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

    /**
     * -----------------
     * Requirement 1.2.1
     * -----------------
     * The client MUST provide a method to add hooks which accepts one or more
     * API-conformant hooks, and appends them to the collection of any previously
     * added hooks. When new hooks are added, previously added hooks are not removed.
     *
     * Adds hooks for evaluation.
     * Hooks are run in the order they're added in the before stage. They are run in
     * reverse order for all other stages.
     */
    public function addHooks(Hook ...$hooks): void
    {
        $this->hooks = array_merge($this->hooks, $hooks);
    }

    /**
     * -----------------
     * Requirement 1.2.2
     * -----------------
     * The client interface MUST define a metadata member or accessor, containing
     * an immutable name field or accessor of type string, which corresponds to
     * the name value supplied during client creation.
     *
     * Returns the metadata for the current resource
     */
    public function getMetadata(): MetadataInterface
    {
        return new Metadata($this->name);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure,
     * with parameters flag key (string, required), default value (boolean | number | string | structure, required),
     * evaluation context (optional), and evaluation options (optional), which returns the flag value.
     */
    public function getBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): bool
    {
        /** @var bool $value */
        $value = $this->getBooleanDetails($flagKey, $defaultValue, $context, $options)->getValue() ?? $defaultValue;

        return $value;
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required),
     * default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation
     * options (optional), which returns an evaluation details structure.
     */
    public function getBooleanDetails(string $flagKey, bool $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): EvaluationDetailsInterface
    {
        return $this->evaluateFlag(FlagValueType::BOOLEAN, $flagKey, $defaultValue, $context, $options);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure,
     * with parameters flag key (string, required), default value (boolean | number | string | structure, required),
     * evaluation context (optional), and evaluation options (optional), which returns the flag value.
     */
    public function getStringValue(string $flagKey, string $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): string
    {
        /** @var string $value */
        $value = $this->getStringDetails($flagKey, $defaultValue, $context, $options)->getValue() ?? $defaultValue;

        return $value;
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required),
     * default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation
     * options (optional), which returns an evaluation details structure.
     */
    public function getStringDetails(string $flagKey, string $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): EvaluationDetailsInterface
    {
        return $this->evaluateFlag(FlagValueType::STRING, $flagKey, $defaultValue, $context, $options);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure,
     * with parameters flag key (string, required), default value (boolean | number | string | structure, required),
     * evaluation context (optional), and evaluation options (optional), which returns the flag value.
     *
     * -----------------
     * Conditional Requirement 1.3.2.1
     * -----------------
     * The client SHOULD provide functions for floating-point numbers and integers, consistent with language idioms.
     */
    public function getIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): int
    {
        /** @var int $value */
        $value = $this->getIntegerDetails($flagKey, $defaultValue, $context, $options)->getValue() ?? $defaultValue;

        return $value;
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required),
     * default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation
     * options (optional), which returns an evaluation details structure.
     */
    public function getIntegerDetails(string $flagKey, int $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): EvaluationDetailsInterface
    {
        return $this->evaluateFlag(FlagValueType::INTEGER, $flagKey, $defaultValue, $context, $options);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure,
     * with parameters flag key (string, required), default value (boolean | number | string | structure, required),
     * evaluation context (optional), and evaluation options (optional), which returns the flag value.
     *
     * -----------------
     * Conditional Requirement 1.3.2.1
     * -----------------
     * The client SHOULD provide functions for floating-point numbers and integers, consistent with language idioms.
     */
    public function getFloatValue(string $flagKey, float $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): float
    {
        /** @var float $value */
        $value = $this->getFloatDetails($flagKey, $defaultValue, $context, $options)->getValue() ?? $defaultValue;

        return $value;
    }

    /**
     * -----------------
     * Requirement 1.4.1
     * -----------------
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required),
     * default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation
     * options (optional), which returns an evaluation details structure.
     */
    public function getFloatDetails(string $flagKey, float $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): EvaluationDetailsInterface
    {
        return $this->evaluateFlag(FlagValueType::FLOAT, $flagKey, $defaultValue, $context, $options);
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure,
     * with parameters flag key (string, required), default value (boolean | number | string | structure, required),
     * evaluation context (optional), and evaluation options (optional), which returns the flag value.
     *
     * @inheritdoc
     */
    public function getObjectValue(string $flagKey, $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null)
    {
        /** @var mixed[] $value */
        $value = $this->getObjectDetails($flagKey, $defaultValue, $context, $options)->getValue() ?? $defaultValue;

        return $value;
    }

    /**
     * -----------------
     * Requirement 1.3.1
     * -----------------
     * The client MUST provide methods for typed flag evaluation, including boolean, numeric, string, and structure, with parameters flag key (string, required), default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation options (optional), which returns the flag val4e.
     *
     * The client MUST provide methods for detailed flag value evaluation with parameters flag key (string, required),
     * default value (boolean | number | string | structure, required), evaluation context (optional), and evaluation
     * options (optional), which returns an evaluation details structure.
     *
     * @inheritdoc
     */
    public function getObjectDetails(string $flagKey, $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptionsInterface $options = null): EvaluationDetailsInterface
    {
        return $this->evaluateFlag(FlagValueType::OBJECT, $flagKey, $defaultValue, $context, $options);
    }

    /**
     * -----------------
     * Requirement 1.4.9
     * -----------------
     * Methods, functions, or operations on the client MUST NOT throw exceptions, or otherwise abnormally terminate. Flag evaluation calls must always return the default value in the event of abnormal execution. Exceptions include functions or methods for the purposes for configuration or setup.
     *
     * @param bool|string|int|float|DateTime|mixed[]|null $defaultValue
     */
    private function evaluateFlag(
        string $flagValueType,
        string $flagKey,
        bool | string | int | float | DateTime | array | null $defaultValue,
        ?EvaluationContextInterface $invocationContext = null,
        ?EvaluationOptionsInterface $options = null,
    ): EvaluationDetailsInterface {
        $api = $this->api;
        $provider = $api->getProvider();
        /** @var EvaluationOptionsInterface $options */
        $options = $options ?? new EvaluationOptions();
        $hookHints = $options->getHookHints() ?? new HookHints();
        $hookExecutor = new HookExecutor($this->logger);

        $mergedContext = EvaluationContext::merge(
            $api->getEvaluationContext(),
            $this->getEvaluationContext(),
            $invocationContext,
        );

        $hookContext = HookContextFactory::from(
            $flagKey,
            $flagValueType,
            $defaultValue,
            $mergedContext,
            $this->getMetadata(),
            $provider->getMetadata(),
        );

        // -----------------
        // Requirement 4.4.2
        // -----------------
        // Hooks MUST be evaluated in the following order:
        //   before: API, Client, Invocation, Provider
        //   after: Provider, Invocation, Client, API
        //   error (if applicable): Provider, Invocation, Client, API
        //   finally: Provider, Invocation, Client, API
        $mergedBeforeHooks = array_merge(
            $api->getHooks(),
            $this->getHooks(),
            $options->getHooks(),
            $provider->getHooks(),
        );

        $mergedRemainingHooks = array_reverse(array_merge([], $mergedBeforeHooks));

        try {
            $contextFromBeforeHook = $hookExecutor->beforeHooks($flagValueType, $hookContext, $mergedBeforeHooks, $hookHints);

            $mergedContext = EvaluationContext::merge($mergedContext, $contextFromBeforeHook);
            $hookContext = (new HookContextBuilder())
                                ->withFlagKey($hookContext->getFlagKey())
                                ->withType($hookContext->getType())
                                ->withDefaultValue($hookContext->getDefaultValue())
                                ->withEvaluationContext($mergedContext)
                                ->withClientMetadata($hookContext->getClientMetadata())
                                ->withProviderMetadata($hookContext->getProviderMetadata())
                                ->build();

            $providerStatus = $this->getProviderStatus();
            if ($providerStatus->equals(ProviderStatus::NOT_READY())) {
                throw new ResolutionError(ErrorCode::PROVIDER_NOT_READY(), 'Provider is not ready.');
            }

            if ($providerStatus->equals(ProviderStatus::FATAL())) {
                throw new ResolutionError(ErrorCode::PROVIDER_FATAL(), 'Provider has entered a fatal state.');
            }

            $resolutionDetails = $this->createProviderEvaluation(
                $flagValueType,
                $flagKey,
                $defaultValue,
                $provider,
                $mergedContext,
            );

            if (!$resolutionDetails->getError() && !ValueTypeValidator::is($flagValueType, $resolutionDetails->getValue())) {
                throw new InvalidResolutionValueError($flagValueType);
            }

            $details = EvaluationDetailsFactory::fromResolution($flagKey, $resolutionDetails);

            $hookExecutor->afterHooks($flagValueType, $hookContext, $resolutionDetails, $mergedRemainingHooks, $hookHints);
        } catch (Throwable $err) {
            $this->getLogger()->error(
                "An error occurred during feature flag evaluation of flag '{flagKey}': {errorMessage}",
                [
                    'flagKey' => $flagKey,
                    'errorMessage' => $err->getMessage(),
                    'exception' => $err,
                ],
            );

            $error = $err instanceof ThrowableWithResolutionError ? $err->getResolutionError() : new ResolutionError(ErrorCode::GENERAL(), $err->getMessage());

            $details = (new EvaluationDetailsBuilder())
                            ->withFlagKey($flagKey)
                            ->withValue($defaultValue)
                            ->withReason(Reason::ERROR)
                            ->withError($error)
                            ->build();

            $hookExecutor->errorHooks($flagValueType, $hookContext, $err, $mergedRemainingHooks, $hookHints);
        } finally {
            $hookExecutor->finallyHooks($flagValueType, $hookContext, $mergedRemainingHooks, $hookHints);
        }

        return $details;
    }

    private function createProviderEvaluation(
        string $type,
        string $key,
        mixed $defaultValue,
        Provider $provider,
        EvaluationContextInterface $context,
    ): ResolutionDetails {
        switch ($type) {
            case FlagValueType::BOOLEAN:
                /** @var bool $defaultValue */
                $defaultValue = $defaultValue;

                return $provider->resolveBooleanValue($key, $defaultValue, $context);
            case FlagValueType::STRING:
                /** @var string $defaultValue */
                $defaultValue = $defaultValue;

                return $provider->resolveStringValue($key, $defaultValue, $context);
            case FlagValueType::INTEGER:
                /** @var int $defaultValue */
                $defaultValue = $defaultValue;

                return $provider->resolveIntegerValue($key, $defaultValue, $context);
            case FlagValueType::FLOAT:
                /** @var float $defaultValue */
                $defaultValue = $defaultValue;

                return $provider->resolveFloatValue($key, $defaultValue, $context);
            case FlagValueType::OBJECT:
                /** @var mixed[] $defaultValue */
                $defaultValue = $defaultValue;

                return $provider->resolveObjectValue($key, $defaultValue, $context);
            default:
                throw new FlagValueTypeError($type);
        }
    }
}
