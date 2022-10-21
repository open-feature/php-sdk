<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use DateTime;
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
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public function withValue($value): ResolutionDetailsBuilder
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
