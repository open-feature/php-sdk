<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface HandlerAdder
{
    public function addHandler(ProviderEvent $event, callable $handler): void;
}
