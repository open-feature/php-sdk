<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\implementation\common\Metadata;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;
use Psr\Log\LoggerAwareTrait;

class NoOpProvider implements Provider
{
    use LoggerAwareTrait;

    private static string $NAME = 'NoOpProvider';

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(self::$NAME);
    }

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

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return [];
    }

    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void
    {
        // no-op
    }
}
