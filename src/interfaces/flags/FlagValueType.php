<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

enum FlagValueType: string
{
    case String = 'STRING';
    case Integer = 'INTEGER';
    case Float = 'FLOAT';
    case Object = 'OBJECT';
    case Boolean = 'BOOLEAN';

    /**
     * @deprecated prefer enum value over const
     */
    public const STRING = 'STRING';
    /**
     * @deprecated prefer enum value over const
     */
    public const INTEGER = 'INTEGER';
    /**
     * @deprecated prefer enum value over const
     */
    public const FLOAT = 'FLOAT';
    /**
     * @deprecated prefer enum value over const
     */
    public const OBJECT = 'OBJECT';
    /**
     * @deprecated prefer enum value over const
     */
    public const BOOLEAN = 'BOOLEAN';
}
