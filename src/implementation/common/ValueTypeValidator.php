<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use DateTime;
use OpenFeature\interfaces\flags\FlagValueType;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

class ValueTypeValidator
{
    /**
     * Validates whether the value is a boolean type
     */
    public static function isBoolean(mixed $value): bool
    {
        return is_bool($value);
    }

    /**
     * Validates whether the value is a string type
     */
    public static function isString(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * Validates whether the value is an integer type
     */
    public static function isInteger(mixed $value): bool
    {
        return is_int($value);
    }

    /**
     * Validates whether the value is a float type
     */
    public static function isFloat(mixed $value): bool
    {
        return is_float($value);
    }

    /**
     * Validates whether the value is a structure
     */
    public static function isStructure(mixed $value): bool
    {
        return self::isArray($value);
    }

    /**
     * Validates whether the value is an Array type
     */
    public static function isArray(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * Validates whether the value is a DateTime type
     */
    public static function isDateTime(mixed $value): bool
    {
        return $value instanceof DateTime;
    }

    /**
     * Validates whether the value is valid for the given type
     */
    public static function is(string $type, mixed $value): bool
    {
        switch ($type) {
            case FlagValueType::BOOLEAN:
                return self::isBoolean($value);
            case FlagValueType::FLOAT:
                return self::isFloat($value);
            case FlagValueType::INTEGER:
                return self::isInteger($value);
            case FlagValueType::STRING:
                return self::isString($value);
            case FlagValueType::OBJECT:
                return self::isStructure($value) || self::isArray($value);
            default:
                return false;
        }
    }
}
