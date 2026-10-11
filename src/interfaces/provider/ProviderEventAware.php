<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * Opt-in marker for lifecycle providers which own their READY and ERROR events.
 */
interface ProviderEventAware extends ProviderEventEmitter, ProviderLifecycle
{
}
