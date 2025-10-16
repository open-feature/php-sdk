<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ThrowableWithResolutionError;

/**
 * FirstMatchStrategy returns the first result from a provider that is not FLAG_NOT_FOUND.
 *
 * This strategy evaluates providers sequentially and:
 * - Skips providers that return FLAG_NOT_FOUND error
 * - Returns the first result that is either successful or has any error other than FLAG_NOT_FOUND
 * - Stops evaluation as soon as a matching result is found
 *
 * @see https://openfeature.dev/specification/appendix-a/#first-match-strategy
 */
class FirstMatchStrategy extends BaseEvaluationStrategy
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
     * Continue to next provider only if current result is FLAG_NOT_FOUND.
     * Stop on first successful result or any other error.
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
        // If evaluation was successful, stop here
        if ($result->isSuccessful()) {
            return false;
        }

        // If there's an error, check if it's FLAG_NOT_FOUND
        $error = $result->getError();
        if ($error !== null) {
            // Check if error is ThrowableWithResolutionError with FLAG_NOT_FOUND
            if ($error instanceof ThrowableWithResolutionError) {
                $resolutionError = $error->getResolutionError();
                if ($resolutionError && $resolutionError->getResolutionErrorCode() === ErrorCode::FLAG_NOT_FOUND()) {
                    // Continue to next provider for FLAG_NOT_FOUND
                    return true;
                }
            }

            // For any other error, stop here
            return false;
        }

        // Continue if no result
        return true;
    }

    /**
     * Returns the first successful result or the first non-FLAG_NOT_FOUND error.
     * If all providers returned FLAG_NOT_FOUND or no results, return error.
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

        // Find first error that is not FLAG_NOT_FOUND
        foreach ($resolutions as $resolution) {
            if ($resolution->hasError()) {
                $error = $resolution->getError();

                // Check if it's NOT FLAG_NOT_FOUND
                $isFlagNotFound = false;
                if ($error instanceof ThrowableWithResolutionError) {
                    $resolutionError = $error->getResolutionError();
                    if ($resolutionError && $resolutionError->getResolutionErrorCode() === ErrorCode::FLAG_NOT_FOUND()) {
                        $isFlagNotFound = true;
                    }
                }

                if (!$isFlagNotFound) {
                    // Return this error
                    return new FinalResult(
                        null,
                        null,
                        [
                            [
                                'providerName' => $resolution->getProviderName(),
                                'error' => $error,
                            ],
                        ],
                    );
                }
            }
        }

        // All providers returned FLAG_NOT_FOUND or no results
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
