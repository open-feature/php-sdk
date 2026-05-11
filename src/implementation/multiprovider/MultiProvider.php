<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider;

use InvalidArgumentException;
use OpenFeature\implementation\flags\EvaluationContext as ImplEvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\BaseEvaluationStrategy;
use OpenFeature\implementation\multiprovider\strategy\FirstMatchStrategy;
use OpenFeature\implementation\multiprovider\strategy\ProviderContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyContext;
use OpenFeature\implementation\provider\AbstractProvider;
use OpenFeature\implementation\provider\Reason;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use OpenFeature\interfaces\provider\RunMode;
use Throwable;

use function array_diff;
use function array_keys;
use function assert;
use function count;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function strtolower;
use function trim;

class MultiProvider extends AbstractProvider
{
    protected static string $NAME = 'MultiProvider';

    /**
     * List of supported keys in each provider data entry.
     *
     * @var array<int, string>
     */
    private static array $supportedProviderData = [
        'name', 'provider',
    ];

    public const NAME = 'MultiProvider';

    /**
     * @var array<string, Provider> Providers indexed by their names.
     */
    protected array $providersByName = [];

    /**
     * The evaluation strategy to use for flag resolution.
     */
    protected BaseEvaluationStrategy $strategy;

    /**
     * Multiprovider constructor.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     * @param BaseEvaluationStrategy|null $strategy Optional strategy instance.
     */
    public function __construct(array $providerData = [], ?BaseEvaluationStrategy $strategy = null)
    {
        $this->validateProviderData($providerData);
        $this->registerProviders($providerData);

        $this->strategy = $strategy ?? new FirstMatchStrategy();
    }

   /**
     * Resolves the flag value for the provided flag key as a boolean
     *
     * @param string $flagKey The flag key to resolve
     * @param bool $defaultValue The default value to return if no provider resolves the flag
     * @param EvaluationContext|null $context The evaluation context
     *
     * @return ResolutionDetails The resolution details
     */
    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return $this->evaluateFlag('boolean', $flagKey, $defaultValue, $context);
    }

    /**
     * Resolves the flag value for the provided flag key as a string
     * * @param string $flagKey The flag key to resolve
     *
     * @param string $defaultValue The default value to return if no provider resolves the flag
     * @param EvaluationContext|null $context The evaluation context
     *
     * @return ResolutionDetails The resolution details
     */
    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return $this->evaluateFlag('string', $flagKey, $defaultValue, $context);
    }

    /**
     * Resolves the flag value for the provided flag key as an integer
     * * @param string $flagKey The flag key to resolve
     *
     * @param int $defaultValue The default value to return if no provider resolves the flag
     * @param EvaluationContext|null $context The evaluation context
     *
     * @return ResolutionDetails The resolution details
     */
    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return $this->evaluateFlag('integer', $flagKey, $defaultValue, $context);
    }

    /**
     * Resolves the flag value for the provided flag key as a float
     * * @param string $flagKey The flag key to resolve
     *
     * @param float $defaultValue The default value to return if no provider resolves the flag
     * @param EvaluationContext|null $context The evaluation context
     *
     * @return ResolutionDetails The resolution details
     */
    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return $this->evaluateFlag('float', $flagKey, $defaultValue, $context);
    }

    /**
     * Resolves the flag value for the provided flag key as an object
     *
     * @param string $flagKey The flag key to resolve
     * @param EvaluationContext|null $context The evaluation context
     * @param mixed[] $defaultValue
     *
     * @return ResolutionDetails The resolution details
     */
    public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return $this->evaluateFlag('object', $flagKey, $defaultValue, $context);
    }

    /**
     * Core evaluation logic that works with the strategy to resolve flags across multiple providers.
     */
    private function evaluateFlag(string $flagType, string $flagKey, mixed $defaultValue, ?EvaluationContext $context): ResolutionDetails
    {
        $context = $context ?? new ImplEvaluationContext();

        // Create base evaluation context
        $strategyContext = new StrategyContext($flagKey, $flagType, $defaultValue, $context);

        if ($this->strategy->runMode === RunMode::PARALLEL) {
            $resolutions = $this->evaluateParallel($strategyContext);
        } else {
            $resolutions = $this->evaluateSequential($strategyContext);
        }

        // Let strategy determine final result
        $finalResult = $this->strategy->determineFinalResult($strategyContext, $resolutions);

        if ($finalResult->isSuccessful()) {
            $details = $finalResult->getDetails();
            if ($details instanceof ResolutionDetails) {
                return $details;
            }
        }

        // Handle error case
        return $this->createErrorResolution($flagKey, $defaultValue, $finalResult->getErrors());
    }

    /**
     * Evaluate providers sequentially based on strategy decisions.
     *
     * @return array<int, ProviderResolutionResult> Array of resolution results from evaluated providers.
     */
    private function evaluateSequential(StrategyContext $baseContext): array
    {
        $resolutions = [];

        foreach ($this->providersByName as $providerName => $provider) {
            $providerContext = new ProviderContext($baseContext, $providerName, $provider);

            // Check if we should evaluate this provider
            if (!$this->strategy->shouldEvaluateThisProvider($providerContext)) {
                continue;
            }

            // Evaluate provider
            $result = $this->evaluateProvider($providerContext, $baseContext);
            $resolutions[] = $result;

            // Check if we should continue to next provider
            if (!$this->strategy->shouldEvaluateNextProvider($providerContext, $result)) {
                break;
            }
        }

        return $resolutions;
    }

    /**
     * Evaluate all providers in parallel (all that pass shouldEvaluateThisProvider).
     *
     * @return array<int, ProviderResolutionResult> Array of resolution results from evaluated providers.
     */
    private function evaluateParallel(StrategyContext $strategyContext): array
    {
        $resolutions = [];

        foreach ($this->providersByName as $providerName => $provider) {
            $providerContext = new ProviderContext($strategyContext, $providerName, $provider);

            // Check if we should evaluate this provider
            if (!$this->strategy->shouldEvaluateThisProvider($providerContext)) {
                continue;
            }

            // Evaluate provider
            $result = $this->evaluateProvider($providerContext, $strategyContext);
            $resolutions[] = $result;
        }

        return $resolutions;
    }

    /**
     * Evaluate a single provider and return result with error handling.
     */
    private function evaluateProvider(ProviderContext $providerContext, StrategyContext $strategyContext): ProviderResolutionResult
    {
        $provider = $providerContext->getProvider();
        $providerName = $providerContext->getProviderName();

        try {
            $flagKey = $strategyContext->getFlagKey();
            $flagType = $strategyContext->getFlagType();
            /** @var bool|string|int|float|array<mixed> $defaultValue */
            $defaultValue = $strategyContext->getDefaultValue();
            $evalContext = $strategyContext->getEvaluationContext();

            $details = match ($flagType) {
                'boolean' => (function () use ($provider, $flagKey, $defaultValue, $evalContext) {
                    assert(is_bool($defaultValue));

                    return $provider->resolveBooleanValue($flagKey, $defaultValue, $evalContext);
                })(),
                'string' => (function () use ($provider, $flagKey, $defaultValue, $evalContext) {
                    assert(is_string($defaultValue));

                    return $provider->resolveStringValue($flagKey, $defaultValue, $evalContext);
                })(),
                'integer' => (function () use ($provider, $flagKey, $defaultValue, $evalContext) {
                    assert(is_int($defaultValue));

                    return $provider->resolveIntegerValue($flagKey, $defaultValue, $evalContext);
                })(),
                'float' => (function () use ($provider, $flagKey, $defaultValue, $evalContext) {
                    assert(is_float($defaultValue));

                    return $provider->resolveFloatValue($flagKey, $defaultValue, $evalContext);
                })(),
                'object' => (function () use ($provider, $flagKey, $defaultValue, $evalContext) {
                    assert(is_array($defaultValue));

                    return $provider->resolveObjectValue($flagKey, $defaultValue, $evalContext);
                })(),
                default => throw new InvalidArgumentException('Unknown flag type: ' . $flagType),
            };

            return new ProviderResolutionResult($providerName, $provider, $details, null);
        } catch (Throwable $error) {
            return new ProviderResolutionResult($providerName, $provider, null, $error);
        }
    }

    /**
     * Create an error resolution with aggregated errors from multiple providers.
     *
     * @param string $flagKey The flag key being evaluated.
     * @param mixed $defaultValue The default value to return.
     * @param array<int, array{providerName: string, error: Throwable}>|null $errors Array of errors encountered during evaluation.
     */
    private function createErrorResolution(string $flagKey, mixed $defaultValue, ?array $errors): ResolutionDetails
    {
        $errorMessage = 'Multi-provider evaluation failed';
        $errorCode = ErrorCode::GENERAL();

        if ($errors !== null && count($errors) > 0) {
            $errorMessage .= ' with ' . count($errors) . ' provider error(s)';
        }

        return (new ResolutionDetailsBuilder())
                            ->withReason(Reason::ERROR)
                            ->withError(new ResolutionError($errorCode, $errorMessage))
                            ->build();
    }

    /**
     * Validate the provider data array.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     *
     * @throws InvalidArgumentException If unsupported keys, invalid names, or duplicate names are found.
     */
    private function validateProviderData(array $providerData): void
    {
        $names = [];

        foreach ($providerData as $entry) {
            // Check for unsupported keys
            if ($unsupportedKeys = array_diff(array_keys($entry), self::$supportedProviderData)) {
                throw new InvalidArgumentException('Unsupported keys: ' . implode(', ', $unsupportedKeys));
            }

            // Check for empty names and duplicates in one pass (case-insensitive)
            if (isset($entry['name'])) {
                $name = trim($entry['name']);
                if ($name === '') {
                    throw new InvalidArgumentException('Provider name cannot be empty');
                }
                $lowerName = strtolower($name);
                if (isset($names[$lowerName])) {
                    throw new InvalidArgumentException("Duplicate provider name: {$name}");
                }
                $names[$lowerName] = true;
            }
        }
    }

    /**
     * Register providers by their names.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     */
    private function registerProviders(array $providerData): void
    {
        $nameCounts = [];

        foreach ($providerData as $entry) {
            $name = isset($entry['name']) && $entry['name'] !== ''
                ? $entry['name']
                : $this->generateUniqueName($entry['provider']->getMetadata()->getName(), $nameCounts);

            if (isset($this->providersByName[$name])) {
                throw new InvalidArgumentException('Duplicate provider name detected during assignment: ' . $name);
            }

            $this->providersByName[$name] = $entry['provider'];
        }
    }

    /**
     * Generate a unique provider name by appending a count suffix if necessary.
     * E.g., if "ProviderA" is used twice, the second instance becomes "ProviderA_2".
     *
     * @param string $baseName The base name of the provider.
     * @param array<string, int> $counts Reference to an associative array tracking name counts.
     *
     * @return string A unique provider name.
     */
    private function generateUniqueName(string $baseName, array &$counts): string
    {
        $counts[$baseName] = ($counts[$baseName] ?? 0) + 1;

        return $counts[$baseName] === 1 ? $baseName : "{$baseName}_{$counts[$baseName]}";
    }
}
