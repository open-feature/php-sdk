<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

use OpenFeature\interfaces\events\EventDetails as EventDetailsInterface;
use OpenFeature\interfaces\events\ProviderEventDetails as ProviderEventDetailsInterface;

final class EventDetails extends ProviderEventDetails implements EventDetailsInterface
{
    private string $providerName;

    public function __construct(string $providerName, ?ProviderEventDetailsInterface $details = null)
    {
        parent::__construct(
            $details === null ? null : $details->getMessage(),
            $details === null ? [] : $details->getFlagsChanged(),
            $details === null ? [] : $details->getEventMetadata(),
            $details === null ? null : $details->getErrorCode(),
        );
        $this->providerName = $providerName;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }
}
