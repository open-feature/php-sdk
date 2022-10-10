<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\interfaces\flags\EvaluationDetails as EvaluationDetailsInterface;
use OpenFeature\interfaces\provider\ErrorCode;

class EvaluationDetails implements EvaluationDetailsInterface
{
    private string $flagKey = '';
    /** @var bool|string|int|float|DateTime|mixed[]|null $value */
    private $value;
    private ?ErrorCode $errorCode = null;
    private ?string $reason = null;
    private ?string $variant = null;

    public function __construct()
    {
        $this->value = null;
    }

    public function getFlagKey(): string
    {
        return $this->flagKey;
    }

    public function setFlagKey(string $flagKey): void
    {
        $this->flagKey = $flagKey;
    }

    /**
     * -----------------
     * Requirement 1.4.2
     * -----------------
     * The evaluation details structure's value field MUST contain the evaluated flag value.
     *
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
