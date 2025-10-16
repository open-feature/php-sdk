<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider;

use InvalidArgumentException;
use OpenFeature\implementation\multiprovider\strategy\BaseEvaluationStrategy;
use OpenFeature\implementation\multiprovider\strategy\FirstMatchStrategy;
use OpenFeature\implementation\multiprovider\strategy\StrategyEvaluationContext;
use OpenFeature\implementation\multiprovider\strategy\StrategyPerProviderContext;
use OpenFeature\implementation\provider\AbstractProvider;
use OpenFeature\implementation\provider\Reason;
use OpenFeature\implementation\provider\ResolutionDetailsBuilder;
use OpenFeature\implementation\provider\ResolutionError;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

use function array_count_values;
use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function strtolower;
use function trim;

class Multiprovider extends AbstractProvider
{
    protected static string $NAME = 'Multiprovider';

    /**
     * List of supported keys in each provider data entry.
     *
     * @var array<int, string>
     */
    private static array $supportedProviderData = [
        'name', 'provider',
    ];

    public const NAME = 'Multiprovider';

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
        $context = $context ?? new \OpenFeature\implementation\flags\EvaluationContext();

        // Create base evaluation context
        $baseContext = new StrategyEvaluationContext($flagKey, $flagType, $defaultValue, $context);

        // Collect results from providers based on strategy
        if ($this->strategy->runMode === 'parallel') {
            $resolutions = $this->evaluateParallel($baseContext);
        } else {
            $resolutions = $this->evaluateSequential($baseContext);
        }

        // Let strategy determine final result
        $finalResult = $this->strategy->determineFinalResult($baseContext, $resolutions);

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
    private function evaluateSequential(StrategyEvaluationContext $baseContext): array
    {
        $resolutions = [];

        foreach ($this->providersByName as $providerName => $provider) {
            $perProviderContext = new StrategyPerProviderContext($baseContext, $providerName, $provider);

            // Check if we should evaluate this provider
            if (!$this->strategy->shouldEvaluateThisProvider($perProviderContext)) {
                continue;
            }

            // Evaluate provider
            $result = $this->evaluateProvider($provider, $providerName, $baseContext);
            $resolutions[] = $result;

            // Check if we should continue to next provider
            if (!$this->strategy->shouldEvaluateNextProvider($perProviderContext, $result)) {
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
    private function evaluateParallel(StrategyEvaluationContext $baseContext): array
    {
        $resolutions = [];

        foreach ($this->providersByName as $providerName => $provider) {
            $perProviderContext = new StrategyPerProviderContext($baseContext, $providerName, $provider);

            // Check if we should evaluate this provider
            if (!$this->strategy->shouldEvaluateThisProvider($perProviderContext)) {
                continue;
            }

            // Evaluate provider
            $result = $this->evaluateProvider($provider, $providerName, $baseContext);
            $resolutions[] = $result;
        }

        return $resolutions;
    }

    /**
     * Evaluate a single provider and return result with error handling.
     */
    private function evaluateProvider(Provider $provider, string $providerName, StrategyEvaluationContext $context): ProviderResolutionResult
    {
        try {
            $flagType = $context->getFlagType();
            /** @var bool|string|int|float|array<mixed>|null $defaultValue */
            $defaultValue = $context->getDefaultValue();
            $evalContext = $context->getEvaluationContext();

            $details = match ($flagType) {
                'boolean' => is_bool($defaultValue)
                ? $provider->resolveBooleanValue($context->getFlagKey(), $defaultValue, $evalContext)
                : throw new InvalidArgumentException('Default value for boolean flag must be bool'),
                'string' => is_string($defaultValue)
                ? $provider->resolveStringValue($context->getFlagKey(), $defaultValue, $evalContext)
                : throw new InvalidArgumentException('Default value for string flag must be string'),
                'integer' => is_int($defaultValue)
                ? $provider->resolveIntegerValue($context->getFlagKey(), $defaultValue, $evalContext)
                : throw new InvalidArgumentException('Default value for integer flag must be int'),
                'float' => is_float($defaultValue)
                ? $provider->resolveFloatValue($context->getFlagKey(), $defaultValue, $evalContext)
                : throw new InvalidArgumentException('Default value for float flag must be float'),
                'object' => is_array($defaultValue)
                ? $provider->resolveObjectValue($context->getFlagKey(), $defaultValue, $evalContext)
                : throw new InvalidArgumentException('Default value for object flag must be array'),
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
        foreach ($providerData as $index => $entry) {
            // check that entry contains only supported keys
            $unSupportedKeys = array_diff(array_keys($entry), self::$supportedProviderData);
            if (count($unSupportedKeys) !== 0) {
                throw new InvalidArgumentException(
                    'Unsupported keys in provider data entry at index ' . $index . ': ' . implode(', ', $unSupportedKeys),
                );
            }
            if (isset($entry['name']) && trim($entry['name']) === '') {
                throw new InvalidArgumentException(
                    'Each provider data entry must have a non-empty string "name" key at index ' . $index,
                );
            }
        }

        $names = array_map(fn ($entry) => $entry['name'] ?? null, $providerData);
        $nameCounts = array_count_values(array_filter($names)); // filter out nulls, count occurrences of each name
        $duplicateNames = array_keys(array_filter($nameCounts, fn ($count) => $count > 1)); // filter by count > 1 to get duplicates

        if ($duplicateNames !== []) {
            throw new InvalidArgumentException('Duplicate provider names found: ' . implode(', ', $duplicateNames));
        }
    }

    /**
     * Register providers by their names.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     *
     * @throws InvalidArgumentException If duplicate provider names are detected during assignment.
     */
    private function registerProviders(array $providerData): void
    {
        $counts = []; // track how many times a base name is used

        foreach ($providerData as $entry) {
            if (isset($entry['name']) && $entry['name'] !== '') {
                $this->providersByName[$entry['name']] = $entry['provider'];
            } else {
                $name = $this->uniqueProviderName($entry['provider']->getMetadata()->getName(), $counts);
                if (isset($this->providersByName[$name])) {
                    throw new InvalidArgumentException('Duplicate provider name detected during assignment: ' . $name);
                }
                $this->providersByName[$name] = $entry['provider'];
            }
        }
    }

    /**
     * Generate a unique provider name by appending a count suffix if necessary.
     * E.g., if "ProviderA" is used twice, the second instance becomes "ProviderA_2".
     *
     * @param string $name The base name of the provider.
     * @param array<string, int> $count Reference to an associative array tracking name counts.
     *
     * @return string A unique provider name.
     */
    private function uniqueProviderName(string $name, array &$count): string
    {
        $key = strtolower($name);
        $count[$key] = ($count[$key] ?? 0) + 1;

        return $count[$key] > 1 ? $name . '_' . $count[$key] : $name;
    }
}
