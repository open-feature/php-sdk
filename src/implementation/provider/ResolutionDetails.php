<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use DateTime;
use OpenFeature\interfaces\provider\ErrorCode;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;

class ResolutionDetails implements ResolutionDetailsInterface
{
    /** @var bool|string|int|float|DateTime|mixed[]|null $value */
    private $value = null;
    private ?ErrorCode $errorCode = null;
    private ?string $reason = null;
    private ?string $variant = null;

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getErrorCode(): ?ErrorCode
    {
        return $this->errorCode;
    }

    public function setErrorCode(?ErrorCode $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    public function setVariant(?string $variant): void
    {
        $this->variant = $variant;
    }
}
