<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

interface ProviderAware
{
    public function getProvider(): Provider;

    public function setProvider(Provider $provider): void;
}
