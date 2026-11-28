<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

use OpenFeature\interfaces\events\ProviderEvent;
use OpenFeature\interfaces\events\ProviderEventDetails;
use Throwable;

trait ProviderEventEmitterTrait
{
    /** @var array<int, callable> */
    private array $providerEventHandlers = [];

    public function addProviderEventHandler(callable $handler): void
    {
        $this->providerEventHandlers[] = $handler;
    }

    public function removeProviderEventHandler(callable $handler): void
    {
        foreach ($this->providerEventHandlers as $index => $registeredHandler) {
            if ($registeredHandler === $handler) {
                unset($this->providerEventHandlers[$index]);
            }
        }
    }

    protected function emitProviderEvent(ProviderEvent $event, ProviderEventDetails $details): void
    {
        foreach ($this->providerEventHandlers as $handler) {
            try {
                $handler($event, $details);
            } catch (Throwable) {
                // Provider event handlers are isolated from one another.
            }
        }
    }
}
