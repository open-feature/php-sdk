<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;

/**
 * FirstSuccessfulStrategy returns the first successful result from a provider.
 *
 * This strategy evaluates providers sequentially and:
 * - Continues through all providers until one succeeds
 * - Ignores all errors and continues to next provider
 * - Returns the first successful result found
 * - If no provider succeeds, returns aggregated errors from all providers
 *
 * @see https://openfeature.dev/specification/appendix-a/#first-successful-strategy
 */
class FirstSuccessfulStrategy extends BaseEvaluationStrategy
{
    public string $runMode = 'sequential';

    /**
     * All providers should be evaluated by default.
     *
     * @param StrategyPerProviderContext $context Context for the specific provider being evaluated
     *
     * @return bool True to evaluate this provider, false to skip
     */
    public function shouldEvaluateThisProvider(
        StrategyPerProviderContext $context,
    ): bool {
        return true;
    }

    /**
     * Always continue to next provider unless we found a successful result.
     * Errors do not stop evaluation in this strategy.
     *
     * @param StrategyPerProviderContext $context Context for the specific provider just evaluated
     * @param ProviderResolutionResult $result Result from the provider that was just evaluated
     *
     * @return bool True to continue to next provider, false to stop evaluation
     */
    public function shouldEvaluateNextProvider(
        StrategyPerProviderContext $context,
        ProviderResolutionResult $result,
    ): bool {
        // If we found a successful result, stop here
        // Otherwise, continue to next provider (even if there was an error)
        return $result->isSuccessful();
    }

    /**
     * Returns the first successful result.
     * If no provider succeeds, returns all errors aggregated.
     *
     * @param StrategyEvaluationContext $context Context for the overall evaluation
     * @param array<int, ProviderResolutionResult> $resolutions Array of resolution results from all providers
     *
     * @return FinalResult The final result of the evaluation
     */
    public function determineFinalResult(
        StrategyEvaluationContext $context,
        array $resolutions,
    ): FinalResult {
        // Find first successful resolution
        foreach ($resolutions as $resolution) {
            if ($resolution->isSuccessful()) {
                return new FinalResult(
                    $resolution->getDetails(),
                    $resolution->getProviderName(),
                    null,
                );
            }
        }

        // No successful results, aggregate all errors
        $errors = [];
        foreach ($resolutions as $resolution) {
            if ($resolution->hasError()) {
                $errors[] = [
                    'providerName' => $resolution->getProviderName(),
                    'error' => $resolution->getError(),
                ];
            }
        }

        return new FinalResult(null, null, $errors ?: null);
    }
}
