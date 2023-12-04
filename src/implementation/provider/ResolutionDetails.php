<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;
use OpenFeature\interfaces\provider\ResolutionError;

class ResolutionDetails implements ResolutionDetailsInterface
{
    /** @var bool|string|int|float|mixed[]|null $value */
    private bool | string | int | float | array | null $value = null;
    private ?ResolutionError $error = null;
    private ?string $reason = null;
    private ?string $variant = null;

    /**
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
