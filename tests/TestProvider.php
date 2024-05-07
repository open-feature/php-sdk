<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\implementation\provider\AbstractProvider;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HooksAwareTrait;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Psr\Log\LoggerAwareTrait;

class TestProvider extends AbstractProvider
{
    use HooksAwareTrait;
    use LoggerAwareTrait;

    protected static string $NAME = 'TestProvider';

    /**
     * Resolves the flag value for the provided flag key as a boolean
     */
    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    /**
     * Resolves the flag value for the provided flag key as a string
     */
    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    /**
     * Resolves the flag value for the provided flag key as an integer
     */
    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    /**
     * Resolves the flag value for the provided flag key as a float
     */
    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    /**
     * @param mixed[] $defaultValue
     */
    public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }
}
