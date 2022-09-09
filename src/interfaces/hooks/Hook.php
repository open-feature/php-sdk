<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

/**
 * An extension point which can run around flag resolution. They are intended to be used as a way to add custom logic
 * to the lifecycle of flag evaluation.
 */
interface Hook
{
    /**
     * Runs before flag is resolved.
     *
     * @param HookContext $context Information about the particular flag evaluation
     * @param HookHints $hints An immutable mapping of data for users to communicate to the hooks.
     *
     * @return ?EvaluationContext An optional EvaluationContext. If returned, it will be merged with the EvaluationContext
     *                            instances from other hooks, the client and API.
     */
    public function before(HookContext $context, HookHints $hints): ?EvaluationContext;

    /**
     * Runs after a flag is resolved.
     *
     * @param HookContext $context Information about the particular flag evaluation
     * @param ResolutionDetails $details Information about how the flag was resolved, including any resolved values.
     * @param HookHints $hints An immutable mapping of data for users to communicate to the hooks.
     */
    public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void;

    /**
     * Run when evaluation encounters an error. This will always run. Errors thrown will be swallowed.
     *
     * @param HookContext $context Information about the particular flag evaluation
     * @param Throwable $error The exception that was thrown.
     * @param HookHints $hints An immutable mapping of data for users to communicate to the hooks.
     */
    public function error(HookContext $context, Throwable $error, HookHints $hints): void;

    /**
     * Run after flag evaluation, including any error processing. This will always run. Errors will be swallowed.
     *
     * @param HookContext $context Information about the particular flag evaluation
     * @param HookHints $hints An immutable mapping of data for users to communicate to the hooks.
     */
    public function finally(HookContext $context, HookHints $hints): void;

    /**
     * Determines whether the hook should be run for the flag value type returned.
     *
     * @param string $flagValueType The type of flag value
     */
    public function supportsFlagValueType(string $flagValueType): bool;
}
