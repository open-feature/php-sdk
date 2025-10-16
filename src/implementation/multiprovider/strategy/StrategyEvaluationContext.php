<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use OpenFeature\interfaces\flags\EvaluationContext;

/**
 * Context information for the overall evaluation across all providers.
 */
class StrategyEvaluationContext
{
    public function __construct(
        private string $flagKey,
        private string $flagType,
        private mixed $defaultValue,
        private EvaluationContext $evaluationContext,
    ) {
    }

    public function getFlagKey(): string
    {
        return $this->flagKey;
    }

    public function getFlagType(): string
    {
        return $this->flagType;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getEvaluationContext(): EvaluationContext
    {
        return $this->evaluationContext;
    }
}
