<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\implementation\common\Metadata;
use OpenFeature\implementation\provider\ResolutionDetailsFactory;
use OpenFeature\interfaces\common\Metadata as MetadataInterface;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HooksAwareTrait;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Psr\Log\LoggerAwareTrait;

class TestProvider implements Provider
{
    use HooksAwareTrait;
    use LoggerAwareTrait;

    public const NAME = 'TestProvider';

    public function getMetadata(): MetadataInterface
    {
        return new Metadata(self::NAME);
    }

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
     * Resolves the flag value for the provided flag key as an object
     *
     * @param mixed[] $defaultValue
     */
    public function resolveObjectValue(string $flagKey, $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        return ResolutionDetailsFactory::fromSuccess($defaultValue);
    }
}
