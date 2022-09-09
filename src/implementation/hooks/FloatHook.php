<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use OpenFeature\interfaces\flags\FlagValueType;

abstract class FloatHook extends AbstractHook
{
    public function supportsFlagValueType(string $flagValueType): bool
    {
        return $flagValueType === FlagValueType::FLOAT;
    }
}
