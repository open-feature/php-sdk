<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use function array_is_list;
use function array_keys;

class ArrayHelper
{
    /**
     * Returns a list of the keys from an associative array
     *
     * Non-associative arrays will return empty lists
     *
     * @param mixed[] $array
     *
     * @return array<int, string>
     */
    public static function getStringKeys(array $array): array
    {
        return array_is_list($array) ? [] : array_keys($array);
    }
}
