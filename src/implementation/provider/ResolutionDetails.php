<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use DateTime;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;
use OpenFeature\interfaces\provider\ResolutionError;

use function is_array;

class ResolutionDetails implements ResolutionDetailsInterface
{
    /** @var bool|string|int|float|DateTime|mixed[]|null $value */
    private bool | string | int | float | DateTime | array | null $value = null;
    private ?ResolutionError $error = null;
    private ?string $reason = null;
    private ?string $variant = null;
    /** @var array<string,bool|string|int|float>|null $metadata */
    private ?array $metadata = null;

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getValue(): bool | string | int | float | DateTime | array | null
    {
        return $this->value;
    }

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public function setValue(bool | string | int | float | DateTime | array | null $value): void
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

    /**
     * @param array<string,bool|string|int|float>|null $metadata
     */
    public function setMetadata(?array $metadata): void
    {
        if (is_array($metadata)) {
            $this->metadata = [];
            foreach ($metadata as $key => $value) {
                $this->metadata[$key] = $value;
            }
        } else {
            $this->metadata = null;
        }
    }

    /**
     * @return array<string,bool|string|int|float>|null
     */
    public function getMetadata(): ?array
    {
        if ($this->metadata === null) {
            return null;
        }
        /** @var array<string,bool|string|int|float> $metadata */
        $metadata = [];
        foreach ($this->metadata as $key => $value) {
            $metadata[$key] = $value;
        }

        return $metadata;
    }
}
