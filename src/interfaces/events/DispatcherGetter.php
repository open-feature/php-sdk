<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

use Psr\EventDispatcher\EventDispatcherInterface;

interface DispatcherGetter
{
    public function getDispatcher(): EventDispatcherInterface;
}
