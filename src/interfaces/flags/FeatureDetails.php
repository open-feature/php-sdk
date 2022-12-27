<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * -----------------
 * Requirement 1.4.1
 * -----------------
 * The client MUST provide methods for detailed flag value evaluation with parameters
 * flag key (string, required), default value (boolean | number | string | structure, required),
 * evaluation context (optional), and evaluation options (optional), which returns an
 * evaluation details structure.
 */
interface FeatureDetails
{
    public function getBooleanDetails(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): EvaluationDetails;

    public function getStringDetails(string $flagKey, string $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): EvaluationDetails;

    public function getIntegerDetails(string $flagKey, int $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): EvaluationDetails;

    public function getFloatDetails(string $flagKey, float $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): EvaluationDetails;

    /**
     * @param mixed[] $defaultValue
     */
    public function getObjectDetails(string $flagKey, $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): EvaluationDetails;
}
