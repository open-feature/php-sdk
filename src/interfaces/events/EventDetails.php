<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface EventDetails extends ProviderEventDetails
{
    public function getProviderName(): string;
}
