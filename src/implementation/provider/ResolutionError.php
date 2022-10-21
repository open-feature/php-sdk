<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ResolutionError as ResolutionErrorInterface;

class ResolutionError implements ResolutionErrorInterface
{
    private ErrorCode $code;
    private ?string $message;

    public function __construct(ErrorCode $code, ?string $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function getCode(): ErrorCode
    {
        return $this->code;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
