<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\provider\Provider;

trait ProviderAwareTrait
{
    private ?Provider $provider = null;

    public function getProvider(): Provider
    {
        return $this->provider ?? new NoOpProvider();
    }

    public function setProvider(Provider $provider): void
    {
        $this->provider = $provider;
    }
}
