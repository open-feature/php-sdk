<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\provider\ResolutionDetails;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

enum FlagValueType: string
{
    case String = 'STRING';
    case Integer = 'INTEGER';
    case Float = 'FLOAT';
    case Object = 'OBJECT';
    case Boolean = 'BOOLEAN';

    public static function tryFromResolutionDetails(ResolutionDetails $resolutionDetails): ?self
    {
        $value = $resolutionDetails->getValue();

        return match (true) {
            is_bool($value) => self::Boolean,
            is_float($value) => self::Float,
            is_int($value) => self::Integer,
            is_string($value) => self::String,
            is_array($value) => self::Object,
            default => null,
        };
    }

    /** @deprecated prefer enum value over const */
    public const STRING = self::String;
    /** @deprecated prefer enum value over const */
    public const INTEGER = self::Integer;
    /** @deprecated prefer enum value over const */
    public const FLOAT = self::Float;
    /** @deprecated prefer enum value over const */
    public const OBJECT = self::Object;
    /** @deprecated prefer enum value over const */
    public const BOOLEAN = self::Boolean;
}
