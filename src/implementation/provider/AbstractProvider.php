<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\implementation\common\Metadata;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HooksAware;
use OpenFeature\interfaces\hooks\HooksAwareTrait;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ProviderState;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractProvider implements HooksAware, Provider
{
    use HooksAwareTrait;
    use LoggerAwareTrait;

    protected static string $NAME = 'AbstractProvider';

    protected ProviderState $status = ProviderState::NOT_READY;

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(self::$NAME);
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
     * The default is that this is not required, and must be overridden by the implementing provider
     */
    public function dispose(): void
    {
        $this->status = ProviderState::NOT_READY;
    }

    /**
     * The default is that this is not required, and must be overridden by the implementing provider
     */
    public function initialize(EvaluationContext $evaluationContext): void
    {
        $this->status = ProviderState::READY;
    }

    public function getStatus(): ProviderState
    {
        return $this->status;
    }
}
