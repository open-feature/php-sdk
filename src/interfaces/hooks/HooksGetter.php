<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

interface HooksGetter
{
    /**
     * @return Hook[]
     */
    public function getHooks(): array;
}
