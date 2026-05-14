<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use MyCLabs\Enum\Enum;

/**
 * Run mode for multi-provider evaluation strategies.
 *
 * @method static RunMode SEQUENTIAL()
 * @method static RunMode PARALLEL()
 * @extends Enum<string>
 * @psalm-immutable
 */
final class RunMode extends Enum
{
    public const SEQUENTIAL = 'sequential';
    public const PARALLEL = 'parallel';
}
