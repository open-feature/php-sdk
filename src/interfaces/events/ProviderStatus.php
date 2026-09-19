<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

use MyCLabs\Enum\Enum;

/**
 * @method static ProviderStatus NOT_READY()
 * @method static ProviderStatus READY()
 * @method static ProviderStatus STALE()
 * @method static ProviderStatus ERROR()
 * @method static ProviderStatus FATAL()
 * @extends Enum<string>
 * @psalm-immutable
 */
final class ProviderStatus extends Enum
{
    public const NOT_READY = 'NOT_READY';
    public const READY = 'READY';
    public const STALE = 'STALE';
    public const ERROR = 'ERROR';
    public const FATAL = 'FATAL';
}
