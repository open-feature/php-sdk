<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

use League\Event\HasEventName;

interface Event extends HasEventName
{
    public function getDetails(): EventDetails;
}
