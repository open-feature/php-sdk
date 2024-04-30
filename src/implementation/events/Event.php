<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

use OpenFeature\interfaces\events\Event as EventInterface;
use OpenFeature\interfaces\events\EventDetails;

class Event implements EventInterface
{
    public function __construct(private string $eventName, private EventDetails $details)
    {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function getDetails(): EventDetails
    {
        return $this->details;
    }
}
