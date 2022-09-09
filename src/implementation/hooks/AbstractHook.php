<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

abstract class AbstractHook implements Hook
{
    abstract public function before(HookContext $context, HookHints $hints): ?EvaluationContext;

    abstract public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void;

    abstract public function error(HookContext $context, Throwable $error, HookHints $hints): void;

    abstract public function finallyAfter(HookContext $context, HookHints $hints): void;

    abstract public function supportsFlagValueType(string $flagValueType): bool;
}
