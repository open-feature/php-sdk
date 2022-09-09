<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\hooks\HooksGetter;

/**
 * -----------------
 * Requirement 1.5.1
 * -----------------
 * The evaluation options structure's hooks field denotes an ordered
 * collection of hooks that the client MUST execute for the respective
 * flag evaluation, in addition to those already configured.
 */
interface EvaluationOptions extends HooksGetter
{
    /**
     * -----------------
     * Requirement 4.5.1
     * -----------------
     * Flag evaluation options MAY contain hook hints, a map of data to
     * be provided to hook invocations.
     */
    public function getHookHints(): ?HookHints;
}
