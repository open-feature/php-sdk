<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * A pseudo-enumerator to support PHP 7.x
 *
 * TODO: Bump to PHP 8.x + support after EOL with
 * native enum implementation
 */
class FlagValueType
{
    public const STRING = 'STRING';
    public const INTEGER = 'INTEGER';
    public const FLOAT = 'FLOAT';
    public const OBJECT = 'OBJECT';
    public const BOOLEAN = 'BOOLEAN';
}
