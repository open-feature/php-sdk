<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\EvaluationDetails as EvaluationDetailsInterface;
use OpenFeature\interfaces\provider\ResolutionError;

class EvaluationDetails implements EvaluationDetailsInterface
{
    private string $flagKey = '';

    /** @var bool|string|int|float|mixed[]|null $value */
    private bool | string | int | float | array | null $value = null;
    private ?ResolutionError $error = null;
    private ?string $reason = null;
    private ?string $variant = null;

    public function __construct()
    {
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
     * @return bool|string|int|float|mixed[]|null
     */
    public function getValue(): bool | string | int | float | array | null
    {
        return $this->value;
    }

    /**
     * @param bool|string|int|float|mixed[]|null $value
     */
    public function setValue(bool | string | int | float | array | null $value): void
    {
        $this->value = $value;
    }

    public function getError(): ?ResolutionError
    {
        return $this->error;
    }

    public function setError(?ResolutionError $error): void
    {
        $this->error = $error;
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
