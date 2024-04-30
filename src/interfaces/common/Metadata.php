<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

interface Metadata
{
    public function getDomain(): string;

    /**
     * @deprecated use getDomain
     */
    public function getName(): string;
}
