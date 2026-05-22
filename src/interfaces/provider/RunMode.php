<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use MyCLabs\Enum\Enum;

/**
 * Run mode for multi-provider evaluation strategies.
 *
 * @method static RunMode SEQUENTIAL()
 * @method static RunMode EVALUATE_ALL()
 * @extends Enum<string>
 * @psalm-immutable
 */
final class RunMode extends Enum
{
    public const SEQUENTIAL = 'sequential';
    public const EVALUATE_ALL = 'evaluate_all';
}
