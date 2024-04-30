<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

interface Disposable
{
    public function dispose(): void;
}
