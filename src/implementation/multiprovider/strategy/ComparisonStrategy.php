<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\interfaces\provider\RunMode;
use Throwable;

use function count;

/**
 * ComparisonStrategy requires all providers to agree on a value.
 *
 * This strategy evaluates all providers (typically in parallel) and:
 * - If all providers return the same value, returns that value
 * - If providers disagree, returns result from the configured fallback provider
 * - Optionally calls an onMismatch callback when values don't match
 * - Useful for migration scenarios or validating provider consistency
 *
 * @see https://openfeature.dev/specification/appendix-a/#comparison-strategy
 */
class ComparisonStrategy extends BaseEvaluationStrategy
{
    public string $runMode = RunMode::PARALLEL;

    /**
     * @param string|null $fallbackProviderName Name of provider to use when results don't match
     * @param callable|null $onMismatch Optional callback when mismatch occurs: fn(array $resolutions): void
     */
    public function __construct(
        private ?string $fallbackProviderName = null,
        private $onMismatch = null,
    ) {
    }

    public function getFallbackProviderName(): ?string
    {
        return $this->fallbackProviderName;
    }

    public function getOnMismatch(): ?callable
    {
        return $this->onMismatch;
    }

    /**
     * All providers should be evaluated by default.
     * This allows for comparison of results across providers.
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
     * In parallel mode, this is not called.
     * If somehow running sequentially, always continue to evaluate all providers.
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
        return true;
    }

    /**
     * Compares all successful results.
     * If they match, returns the common value.
     * If they don't match, returns fallback provider result or first result.
     * If no successful results, returns aggregated errors.
     *
     * @param StrategyEvaluationContext $context Context for the overall evaluation
     * @param ProviderResolutionResult[] $resolutions Array of resolution results from all providers
     *
     * @return FinalResult The final result of the evaluation
     */
    public function determineFinalResult(
        StrategyEvaluationContext $context,
        array $resolutions,
    ): FinalResult {
        // Separate successful results from errors
        $successfulResults = [];
        $errors = [];

        foreach ($resolutions as $resolution) {
            if ($resolution->hasError()) {
                $err = $resolution->getError();
                if ($err instanceof Throwable) {
                    $errors[] = [
                        'providerName' => $resolution->getProviderName(),
                        'error' => $err,
                    ];
                }
            } else {
                $successfulResults[] = $resolution;
            }
        }

        // If no successful results, return errors
        if (count($successfulResults) === 0) {
            return new FinalResult(null, null, $errors !== [] ? $errors : null);
        }

        // If only one successful result, return it
        if (count($successfulResults) === 1) {
            $result = $successfulResults[0];

            return new FinalResult(
                $result->getDetails(),
                $result->getProviderName(),
                null,
            );
        }

        // Compare all successful values
        $firstDetails = $successfulResults[0]->getDetails();
        $firstValue = $firstDetails ? $firstDetails->getValue() : null;
        $allMatch = true;

        foreach ($successfulResults as $result) {
            $details = $result->getDetails();
            if (!$details || $details->getValue() !== $firstValue) {
                $allMatch = false;

                break;
            }
        }

        // If all values match, return the first one
        if ($allMatch) {
            $result = $successfulResults[0];

            return new FinalResult(
                $result->getDetails(),
                $result->getProviderName(),
                null,
            );
        }

        // Values don't match - call onMismatch callback if provided
        $onMismatch = $this->getOnMismatch();
        if ($onMismatch !== null) {
            try {
                $onMismatch($successfulResults);
            } catch (Throwable $e) {
                // Ignore errors from callback
            }
        }

        // Return fallback provider result if configured
        $fallbackProviderName = $this->getFallbackProviderName();
        if ($fallbackProviderName !== null) {
            foreach ($successfulResults as $result) {
                if ($result->getProviderName() === $fallbackProviderName) {
                    return new FinalResult(
                        $result->getDetails(),
                        $result->getProviderName(),
                        null,
                    );
                }
            }
        }

        // No fallback configured or fallback not found, return first result
        $result = $successfulResults[0];

        return new FinalResult(
            $result->getDetails(),
            $result->getProviderName(),
            null,
        );
    }
}
