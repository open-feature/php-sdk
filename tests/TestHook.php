<?php

declare(strict_types=1);

namespace OpenFeature\Test;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

class TestHook implements Hook
{
    public function before(HookContext $context, HookHints $hints): ?EvaluationContext
    {
        return null;
    }

    public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
    {
    }

    public function error(HookContext $context, Throwable $error, HookHints $hints): void
    {
    }

    public function finally(HookContext $context, HookHints $hints): void
    {
    }

    public function supportsFlagValueType(FlagValueType $flagValueType): bool
    {
        return true;
    }
}
