<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

interface HooksSetter
{
    /**
     * @param Hook[] $hooks
     */
    public function setHooks(array $hooks): void;
}
