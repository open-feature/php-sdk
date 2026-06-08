<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use InvalidArgumentException;
use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\RunMode;
use Throwable;

use function count;

/**
 * ComparisonStrategy evaluates all providers and compares their values.
 *
 * This strategy:
 * - Evaluates ALL providers regardless of individual results
 * - Returns errors immediately if ANY provider errors (fail-fast on errors)
 * - If all providers return the same value, returns that common value
 * - If providers disagree, returns the fallback provider's value
 * - Optionally calls an onMismatch callback when values don't match
 *
 * Useful for:
 * - A/B testing and validating provider consistency during migrations
 * - Comparing new provider implementations against trusted baselines
 * - Detecting configuration drift across multiple sources
 *
 * @see https://openfeature.dev/specification/appendix-a/#comparison-strategy
 */
class ComparisonStrategy extends BaseEvaluationStrategy
{
    public string $runMode = RunMode::EVALUATE_ALL;

    /**
     * @param Provider $fallbackProvider Provider to use when results don't match (required)
     * @param callable|null $onMismatch Optional callback when mismatch occurs: fn(array $resolutions): void
     */
    public function __construct(
        private Provider $fallbackProvider,
        private $onMismatch = null,
    ) {
    }

    public function getFallbackProvider(): Provider
    {
        return $this->fallbackProvider;
    }

    public function getOnMismatch(): ?callable
    {
        return $this->onMismatch;
    }

    /**
     * All providers should be evaluated.
     * This allows for comparison of results across providers.
     *
     * @param ProviderContext $context Context for the specific provider being evaluated
     *
     * @return bool True to evaluate this provider
     */
    public function shouldEvaluateThisProvider(
        ProviderContext $context,
    ): bool {
        return true;
    }

    /**
     * When using EVALUATE_ALL mode, this method is not called during evaluation.
     * Always returns true to ensure all providers are evaluated.
     *
     * @param ProviderContext $context Context for the specific provider just evaluated
     * @param ProviderResolutionResult $result Result from the provider that was just evaluated
     *
     * @return bool True to continue to next provider
     */
    public function shouldEvaluateNextProvider(
        ProviderContext $context,
        ProviderResolutionResult $result,
    ): bool {
        return true;
    }

    /**
     * Compares all results for consistency.
     * Fail-fast: returns all errors immediately if ANY provider has an error.
     * If all succeed and values agree, returns the agreed value.
     * If all succeed but values disagree, invokes callback and returns fallback provider's value.
     *
     * @param StrategyContext $context Context for the overall evaluation
     * @param ProviderResolutionResult[] $resolutions Array of resolution results from all providers
     *
     * @return FinalResult The final result of the evaluation
     *
     * @throws InvalidArgumentException If fallback provider not found in results
     */
    public function determineFinalResult(
        StrategyContext $context,
        array $resolutions,
    ): FinalResult {
        if (count($resolutions) === 0) {
            throw new InvalidArgumentException('No resolution results provided');
        }

        $fallbackResolution = null;
        $finalResolution = null;
        $value = null;
        $valueSet = false;
        $mismatch = false;

        foreach ($resolutions as $index => $resolution) {
            // Fail-fast: if ANY provider has an error, return all errors immediately
            if ($resolution->hasError()) {
                return new FinalResult(null, null, $this->aggregateErrors($resolutions));
            }

            // Track the fallback provider's resolution
            if ($resolution->getProvider() === $this->fallbackProvider) {
                $fallbackResolution = $resolution;
            }

            // Track the first resolution
            if ($index === 0) {
                $finalResolution = $resolution;
            }

            // Check for value mismatch using strict equality
            $details = $resolution->getDetails();
            if ($details !== null) {
                if ($valueSet && $value !== $details->getValue()) {
                    $mismatch = true;
                } else {
                    $value = $details->getValue();
                    $valueSet = true;
                }
            }
        }

        // Fallback provider must be found in results
        if ($fallbackResolution === null) {
            throw new InvalidArgumentException('Fallback provider not found in resolution results');
        }

        // Final resolution must exist
        if ($finalResolution === null) {
            throw new InvalidArgumentException('Final resolution not found in resolution results');
        }

        // If there's a value mismatch, invoke callback and use fallback provider
        if ($mismatch) {
            if ($this->onMismatch !== null) {
                try {
                    ($this->onMismatch)($resolutions);
                } catch (Throwable $e) {
                    // Ignore errors from callback
                }
            }

            return new FinalResult(
                $fallbackResolution->getDetails(),
                $fallbackResolution->getProviderName(),
                null,
            );
        }

        // All values match, return the first resolution
        return new FinalResult(
            $finalResolution->getDetails(),
            $finalResolution->getProviderName(),
            null,
        );
    }
}
