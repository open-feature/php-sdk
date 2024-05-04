<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use OpenFeature\interfaces\common\Disposable;
use OpenFeature\interfaces\common\MetadataGetter;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\HooksGetter;
use Psr\EventDispatcher\EventDispatcherInterface;
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
 *
 * -----------------
 * Requirement 2.4.1
 * -----------------
 * The provider MAY define an initialize function which accepts the global evaluation
 * context as an argument and performs initialization logic relevant to the provider.
 *
 * -----------------
 * Requirement 2.5.1
 * -----------------
 * The provider MAY define a shutdown function to perform whatever cleanup is
 * necessary for the implementation.
 */
interface Provider extends Disposable, HooksGetter, Initializable, LoggerAwareInterface, MetadataGetter
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
    public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetails;

    /**
     * -----------------
     * Requirement 2.4.2
     * -----------------
     * The provider MAY define a status field/accessor which indicates the readiness
     * of the provider, with possible values NOT_READY, READY, or ERROR.
     */
    public function getStatus(): ProviderState;

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}
