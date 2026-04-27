<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider\strategy;

use InvalidArgumentException;
use OpenFeature\interfaces\flags\EvaluationContext;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

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
        // Invariant check: flagType must match defaultValue type
        switch ($flagType) {
            case 'boolean':
                if (!is_bool($defaultValue)) {
                    throw new InvalidArgumentException('Default value for boolean flag must be bool');
                }

                break;
            case 'string':
                if (!is_string($defaultValue)) {
                    throw new InvalidArgumentException('Default value for string flag must be string');
                }

                break;
            case 'integer':
                if (!is_int($defaultValue)) {
                    throw new InvalidArgumentException('Default value for integer flag must be int');
                }

                break;
            case 'float':
                if (!is_float($defaultValue)) {
                    throw new InvalidArgumentException('Default value for float flag must be float');
                }

                break;
            case 'object':
                if (!is_array($defaultValue)) {
                    throw new InvalidArgumentException('Default value for object flag must be array');
                }

                break;
            default:
                throw new InvalidArgumentException('Unknown flag type: ' . $flagType);
        }
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
