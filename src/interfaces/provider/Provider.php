<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use OpenFeature\interfaces\common\MetadataGetter;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HooksGetter;
use Psr\Log\LoggerAwareInterface;

/**
 * ---------------
 * Requirement 2.1
 * ---------------
 * The provider interface MUST define a metadata member or accessor, containing
 * a name field or accessor of type string, which identifies the provider implementation.
 *
 * ----------------
 * Requirement 2.10
 * ----------------
 * The provider interface MUST define a provider hook mechanism which can be optionally
 * implemented in order to add hook instances to the evaluation life-cycle.
 */
interface Provider extends HooksGetter, LoggerAwareInterface, MetadataGetter
{
    /**
     * Resolves the flag value for the provided flag key as a boolean
     */
    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;

    /**
     * Resolves the flag value for the provided flag key as a string
     */
    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;

    /**
     * Resolves the flag value for the provided flag key as an integer
     */
    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;

    /**
     * Resolves the flag value for the provided flag key as a float
     */
    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;

    /**
     * Resolves the flag value for the provided flag key as an object
     *
     * @param mixed[] $defaultValue
     */
    public function resolveObjectValue(string $flagKey, $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;
}
