<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

interface HooksAdder
{
    public function addHooks(Hook ...$hooks): void;
}
