<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface EventHandlerAware
{
    /** @param callable(EventDetails): void $handler */
    public function addHandler(ProviderEvent $event, callable $handler): void;

    /** @param callable(EventDetails): void $handler */
    public function removeHandler(ProviderEvent $event, callable $handler): void;
}
