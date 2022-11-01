<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use Exception;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ResolutionError as ResolutionErrorInterface;

class ResolutionError extends Exception implements ResolutionErrorInterface
{
    private ErrorCode $resolutionErrorCode;
    private ?string $resolutionErrorMessage;

    public function __construct(ErrorCode $code, ?string $message = null)
    {
        parent::__construct();
        $this->resolutionErrorCode = $code;
        $this->resolutionErrorMessage = $message;
    }

    public function getResolutionErrorCode(): ErrorCode
    {
        return $this->resolutionErrorCode;
    }

    public function getResolutionErrorMessage(): ?string
    {
        return $this->resolutionErrorMessage;
    }
}
