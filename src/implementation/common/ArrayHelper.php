<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use function array_keys;
use function is_int;
use function sizeof;

class ArrayHelper
{
    /**
     * Determines whether the array is indexed or associative
     *
     * @param mixed[] $array
     *
     * @return array<int, string>
     */
    public static function getStringKeys(array $array)
    {
        $keys = array_keys($array);

        if (sizeof($keys) === 0 || is_int($keys[0])) {
            return [];
        }

        /** @var array<int, string> $stringKeys */
        $stringKeys = $keys;

        return $stringKeys;
    }
}
