<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

enum ProviderEvent: string
{
    case READY = 'PROVIDER_READY';
    case ERROR = 'PROVIDER_ERROR';
    case CONFIGURATION_CHANGED = 'PROVIDER_CONFIGURATION_CHANGED';
    case STALE = 'PROVIDER_STALE';
}
