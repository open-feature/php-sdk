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
     *
     * @param mixed $value
     */
    public static function isBoolean($value): bool
    {
        return is_bool($value);
    }

    /**
     * Validates whether the value is a string type
     *
     * @param mixed $value
     */
    public static function isString($value): bool
    {
        return is_string($value);
    }

    /**
     * Validates whether the value is an integer type
     *
     * @param mixed $value
     */
    public static function isInteger($value): bool
    {
        return is_int($value);
    }

    /**
     * Validates whether the value is a float type
     *
     * @param mixed $value
     */
    public static function isFloat($value): bool
    {
        return is_float($value);
    }

    /**
     * Validates whether the value is a structure
     *
     * @param mixed $value
     */
    public static function isStructure($value): bool
    {
        return self::isArray($value);
    }

    /**
     * Validates whether the value is an Array type
     *
     * @param mixed $value
     */
    public static function isArray($value): bool
    {
        return is_array($value);
    }

    /**
     * Validates whether the value is a DateTime type
     *
     * @param mixed $value
     */
    public static function isDateTime($value): bool
    {
        return $value instanceof DateTime;
    }

    /**
     * Validates whether the value is valid for the given type
     *
     * @param mixed $value
     */
    public static function is(string $type, $value): bool
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
