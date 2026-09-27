<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

use MyCLabs\Enum\Enum;

/**
 * @method static ProviderEvent READY()
 * @method static ProviderEvent ERROR()
 * @method static ProviderEvent STALE()
 * @method static ProviderEvent CONFIGURATION_CHANGED()
 * @extends Enum<string>
 * @psalm-immutable
 */
final class ProviderEvent extends Enum
{
    public const READY = 'PROVIDER_READY';
    public const ERROR = 'PROVIDER_ERROR';
    public const STALE = 'PROVIDER_STALE';
    public const CONFIGURATION_CHANGED = 'PROVIDER_CONFIGURATION_CHANGED';
}
