<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use Throwable;

interface ThrowableWithResolutionError extends Throwable
{
    public function getResolutionError(): ResolutionError;
}
