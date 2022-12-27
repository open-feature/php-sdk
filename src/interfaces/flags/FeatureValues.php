<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * -----------------
 * Requirement 1.3.1
 * -----------------
 * The client MUST provide methods for typed flag evaluation, including boolean,
 * numeric, string, and structure, with parameters flag key (string, required),
 * default value (boolean | number | string | structure, required), evaluation
 * context (optional), and evaluation options (optional), which returns the flag
 * value.
 */
interface FeatureValues
{
    public function getBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): ?bool;

    public function getStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): ?string;

    public function getIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): int;

    public function getFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null): float;

    /**
     * @param mixed[] $defaultValue
     *
     * @return mixed[]
     */
    public function getObjectValue(string $flagKey, $defaultValue, ?EvaluationContext $context = null, ?EvaluationOptions $options = null);
}
