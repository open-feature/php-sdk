<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\events\EventDetails;
use OpenFeature\interfaces\events\EventDetails as EventDetailsInterface;
use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderStatus;
use OpenFeature\interfaces\flags\Client;
use OpenFeature\interfaces\flags\EvaluationContext as EvaluationContextInterface;
use OpenFeature\interfaces\flags\EvaluationDetails;
use OpenFeature\interfaces\flags\EvaluationOptions;
use Throwable;

class NoOpClient implements Client
{
    private const CLIENT_NAME = 'no-op-client';

    public function getBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): ?bool
    {
        return $defaultValue;
    }

    public function getBooleanDetails(string $flagKey, bool $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): EvaluationDetails
    {
        return EvaluationDetailsFactory::from($flagKey, $defaultValue);
    }

    public function getStringValue(string $flagKey, string $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): ?string
    {
        return $defaultValue;
    }

    public function getStringDetails(string $flagKey, string $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): EvaluationDetails
    {
        return EvaluationDetailsFactory::from($flagKey, $defaultValue);
    }

    public function getIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): int
    {
        return $defaultValue;
    }

    public function getIntegerDetails(string $flagKey, int $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): EvaluationDetails
    {
        return EvaluationDetailsFactory::from($flagKey, $defaultValue);
    }

    public function getFloatValue(string $flagKey, float $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): float
    {
        return $defaultValue;
    }

    public function getFloatDetails(string $flagKey, float $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): EvaluationDetails
    {
        return EvaluationDetailsFactory::from($flagKey, $defaultValue);
    }

    /**
     * @param mixed[] $defaultValue
     *
     * @return mixed[]
     */
    public function getObjectValue(
        string $flagKey,
        array $defaultValue,
        ?EvaluationContextInterface $context = null,
        ?EvaluationOptions $options = null,
    ): array {
        return $defaultValue;
    }

    public function getObjectDetails(string $flagKey, bool | string | int | float | DateTime | array | null $defaultValue, ?EvaluationContextInterface $context = null, ?EvaluationOptions $options = null): EvaluationDetails
    {
        return EvaluationDetailsFactory::from($flagKey, $defaultValue);
    }

    public function getMetadata(): Metadata
    {
        return new Metadata(self::CLIENT_NAME);
    }

    public function getEvaluationContext(): EvaluationContextInterface
    {
        return new EvaluationContext('no-op-targeting-key');
    }

    public function setEvaluationContext(EvaluationContextInterface $context): void
    {
      // no-op
    }

    public function getProviderStatus(): ProviderStatus
    {
        return ProviderStatus::READY();
    }

    /** @param callable(EventDetailsInterface): void $handler */
    public function addHandler(ProviderEvent $event, callable $handler): void
    {
        if ($event->equals(ProviderEvent::READY())) {
            try {
                $handler(new EventDetails('NoOpProvider'));
            } catch (Throwable) {
                // No-op event handlers must not cause abnormal execution.
            }
        }
    }

    /** @param callable(EventDetailsInterface): void $handler */
    public function removeHandler(ProviderEvent $event, callable $handler): void
    {
      // no-op
    }

    /**
     * @inheritdoc
     */
    public function getHooks(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function setHooks(array $hooks): void
    {
      // no-op
    }
}
