<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * The type of an evaluation context attribute value
 */
enum AttributeType: string
{
    case STRING = 'STRING';
    case INTEGER = 'INTEGER';
    case FLOAT = 'FLOAT';
    case STRUCTURE = 'STRUCTURE';
    case BOOLEAN = 'BOOLEAN';
}
