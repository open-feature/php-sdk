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
        switch (strlen($input)) {
            case 0:
                return '';
            case 1:
                return strtoupper($input);
            default:
                return strtoupper(substr($input, 0, 1)) . substr($input, 1);
        }
    }

    public static function decapitalize(string $input): string
    {
        switch (strlen($input)) {
            case 0:
                return '';
            case 1:
                return strtolower($input);
            default:
                return strtolower(substr($input, 0, 1)) . substr($input, 1);
        }
    }
}
