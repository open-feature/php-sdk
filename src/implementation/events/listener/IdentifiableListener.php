<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events\listener;

abstract class IdentifiableListener
{
    private string $id;

    public function __construct(private callable $listener)
    {
        $this->id = CallableIdentifier::identify($listener);
    }

    public function __invoke(object $event): void
    {
        call_user_func($this->listener, $event);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
