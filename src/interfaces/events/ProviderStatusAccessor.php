<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface ProviderStatusAccessor
{
    public function getProviderStatus(): ProviderStatus;
}
