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

abstract class AbstractProvider implements Provider
{
    use LoggerAwareTrait;

    protected static string $NAME = 'AbstractProvider';

    /** @var Hook[] $hooks */
    private array $hooks = [];

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(static::$NAME);
    }

    abstract public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface;

    abstract public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface;

    abstract public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface;

    abstract public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface;

    /**
     * @param mixed[] $defaultValue
     */
    abstract public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface;

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void
    {
        $this->hooks = $hooks;
    }
}
