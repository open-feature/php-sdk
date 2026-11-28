<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\events\EventHandlerAware;
use OpenFeature\interfaces\events\ProviderStatusAccessor;

/**
 * Optional client capability for provider status and event handling.
 */
interface EventAwareClient extends Client, EventHandlerAware, ProviderStatusAccessor
{
}
