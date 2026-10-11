<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use OpenFeature\interfaces\events\EventHandlerAware;
use OpenFeature\interfaces\events\ProviderStatusAccessor;
use OpenFeature\interfaces\provider\Provider;

/**
 * Optional API capability for provider lifecycle, status, and event handling.
 */
interface ProviderLifecycleAPI extends API, EventHandlerAware, ProviderStatusAccessor
{
    public function getClient(?string $name = null, ?string $version = null): EventAwareClient;

    public function setProviderAndWait(Provider $provider): void;

    public function shutdown(): void;
}
