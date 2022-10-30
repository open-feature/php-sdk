<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;

class NoOpProvider extends AbstractProvider implements Provider
{
    protected static string $NAME = 'NoOpProvider';

    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }

    /**
     * @inheritdoc
     */
    public function resolveObjectValue(string $flagKey, $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }
}
