<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

/**
 * A pseudo-enumerator to support PHP 7.x
 *
 * TODO: Bump to PHP 8.x + support after EOL with
 * native enum implementation
 */
class Reason
{
    public const ERROR = 'Error';
}
