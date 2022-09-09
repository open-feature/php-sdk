<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use OpenFeature\interfaces\hooks\HookContext;

class HookContextTransformer
{
    public static function toImmutable(HookContext $context): ImmutableHookContext
    {
        return new ImmutableHookContext($context);
    }

    public static function toMutable(HookContext $context): ImmutableHookContext
    {
        return new MutableHookContext($context);
    }
}
