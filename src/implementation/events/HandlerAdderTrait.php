<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

trait HandlerAdderTrait
{
    /**
     * @var Array<callable> $handlers
     */
    private array $handlers = [];
}
