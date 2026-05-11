<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\implementation\multiprovider\FinalResult;
use OpenFeature\implementation\multiprovider\ProviderResolutionResult;
use OpenFeature\interfaces\provider\RunMode;
use Throwable;

/**
 * Base class for multi-provider evaluation strategies per OpenFeature specification.
 *
 * @see https://openfeature.dev/specification/appendix-a/#user-defined-custom-strategy
 */
abstract class BaseEvaluationStrategy
{
    public string $runMode = RunMode::SEQUENTIAL;

    /**
     * Determine if the given provider should be evaluated.
     * This is called before evaluating each provider.
     * Return true to evaluate, false to skip.
     *
     * @param ProviderContext $context Context for the specific provider being evaluated
     *
     * @return bool True to evaluate this provider, false to skip
     */
    abstract public function shouldEvaluateThisProvider(
        ProviderContext $context,
    ): bool;

    /**
     * Determine if evaluation should continue to the next provider.
     * This is called after each provider is evaluated.
     * Return true to continue to next provider, false to stop evaluation.
     *
     * @param ProviderContext $context Context for the specific provider just evaluated
     * @param ProviderResolutionResult $result Result from the provider that was just evaluated
     *
     * @return bool True to continue to next provider, false to stop evaluation
     */
    abstract public function shouldEvaluateNextProvider(
        ProviderContext $context,
        ProviderResolutionResult $result,
    ): bool;

    /**
     * Determine the final result of the evaluation.
     * This is called after all providers have been evaluated.
     *
     * @param StrategyContext $context Context for the overall evaluation
     * @param ProviderResolutionResult[] $resolutions Array of resolution results from all providers
     *
     * @return FinalResult The final result of the evaluation
     */
    abstract public function determineFinalResult(
        StrategyContext $context,
        array $resolutions,
    ): FinalResult;

    /**
     * Determine if tracking should be done with the given provider.
     * This is called when a tracking event is triggered.
     * Return true to track with this provider, false to skip tracking.
     *
     * @param ProviderContext $context Context for the specific provider being considered for tracking
     * @param string $trackingEventName Name of the tracking event
     * @param array <string> $trackingEventDetails Details of the tracking event
     *
     * @return bool True to track with this provider, false to skip
     */
    public function shouldTrackWithThisProvider(
        ProviderContext $context,
        string $trackingEventName,
        array $trackingEventDetails,
    ): bool {
        return true;
    }

    /**
     * Aggregate errors from provider resolution results.
     * Filters out successful results and collects error information.
     *
     * @param ProviderResolutionResult[] $resolutions Array of resolution results
     *
     * @return array<int, array{providerName: string, error: Throwable}>|null Array of errors or null if no errors
     */
    protected function aggregateErrors(array $resolutions): ?array
    {
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
            }
        }

        return $errors !== [] ? $errors : null;
    }
}
