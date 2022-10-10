<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use Throwable;

interface ThrowableWithErrorCode extends Throwable
{
    public function getErrorCode(): ErrorCode;
}
