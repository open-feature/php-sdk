<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

trait HooksAwareTrait
{
    /** @var Hook[] $hooks */
    private array $hooks = [];

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void
    {
        $this->hooks = $hooks;
    }
}
