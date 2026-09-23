<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderEventDetails;

interface ProviderEventEmitter
{
    /** @param callable(ProviderEvent, ProviderEventDetails): void $handler */
    public function addProviderEventHandler(callable $handler): void;

    /** @param callable(ProviderEvent, ProviderEventDetails): void $handler */
    public function removeProviderEventHandler(callable $handler): void;
}
