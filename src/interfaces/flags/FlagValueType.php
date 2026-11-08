<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * The type of value a flag evaluation resolves to
 */
enum FlagValueType: string
{
    case STRING = 'STRING';
    case INTEGER = 'INTEGER';
    case FLOAT = 'FLOAT';
    case OBJECT = 'OBJECT';
    case BOOLEAN = 'BOOLEAN';
}
