<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use function strlen;
use function strtolower;
use function strtoupper;
use function substr;

class StringHelper
{
    public static function capitalize(string $input): string
    {
        return match (strlen($input)) {
            0 => '',
            1 => strtoupper($input),
            default => strtoupper(substr($input, 0, 1)) . substr($input, 1),
        };
    }

    public static function decapitalize(string $input): string
    {
        return match (strlen($input)) {
            0 => '',
            1 => strtolower($input),
            default => strtolower(substr($input, 0, 1)) . substr($input, 1),
        };
    }
}
