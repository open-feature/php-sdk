<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;
use OpenFeature\interfaces\provider\ResolutionError;

class ResolutionDetailsBuilder
{
    private ResolutionDetails $details;

    public function __construct()
    {
        $this->details = new ResolutionDetails();
    }

    /**
     * @param bool|string|int|float|mixed[]|null $value
     */
    public function withValue(bool | string | int | float | array | null $value): ResolutionDetailsBuilder
    {
        $this->details->setValue($value);

        return $this;
    }

    public function withError(ResolutionError $errorCode): ResolutionDetailsBuilder
    {
        $this->details->setError($errorCode);

        return $this;
    }

    public function withReason(string $reason): ResolutionDetailsBuilder
    {
        $this->details->setReason($reason);

        return $this;
    }

    public function withVariant(string $variant): ResolutionDetailsBuilder
    {
        $this->details->setVariant($variant);

        return $this;
    }

    public function build(): ResolutionDetailsInterface
    {
        return $this->details;
    }
}
