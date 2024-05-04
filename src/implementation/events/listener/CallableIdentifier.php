<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events\listener;

class CallableIdentifier
{
    public static function identify(callable $fn): string
    {
        return spl_object_hash((object) $fn);
    }
}
