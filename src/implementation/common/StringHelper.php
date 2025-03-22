<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use function lcfirst;
use function ucfirst;

class StringHelper
{
    public static function capitalize(string $input): string
    {
        return ucfirst($input);
    }

    public static function decapitalize(string $input): string
    {
        return lcfirst($input);
    }
}
