<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

interface StringIndexed
{
    /**
     * @return string[]
     */
    public function keys(): array;
}
