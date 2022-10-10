<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\interfaces\flags\EvaluationDetails as EvaluationDetailsInterface;
use OpenFeature\interfaces\provider\ErrorCode;

class EvaluationDetailsBuilder
{
    private EvaluationDetails $details;

    public function __construct()
    {
        $this->details = new EvaluationDetails();
    }

    public function withFlagKey(string $flagKey): EvaluationDetailsBuilder
    {
        $this->details->setFlagKey($flagKey);

        return $this;
    }

    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public function withValue($value): EvaluationDetailsBuilder
    {
        $this->details->setValue($value);

        return $this;
    }

    public function withErrorCode(?ErrorCode $errorCode): EvaluationDetailsBuilder
    {
        $this->details->setErrorCode($errorCode);

        return $this;
    }

    public function withReason(?string $reason): EvaluationDetailsBuilder
    {
        $this->details->setReason($reason);

        return $this;
    }

    public function withVariant(?string $variant): EvaluationDetailsBuilder
    {
        $this->details->setVariant($variant);

        return $this;
    }

    public function build(): EvaluationDetailsInterface
    {
        return $this->details;
    }
}
