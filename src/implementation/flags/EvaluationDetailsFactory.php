<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\EvaluationDetails;
use OpenFeature\interfaces\provider\ResolutionDetails;

class EvaluationDetailsFactory
{
    /**
     * Provides a simple method for building EvaluationDetails from a given value\
     *
     * @param bool|string|int|float|mixed[]|null $value
     */
    public static function from(string $flagKey, bool | string | int | float | array | null $value): EvaluationDetails
    {
        return (new EvaluationDetailsBuilder())
                    ->withFlagKey($flagKey)
                    ->withValue($value)
                    ->build();
    }

    /**
     * Provides a simple method for building EvaluationDetails from Flag Resolution Details
     */
    public static function fromResolution(string $flagKey, ResolutionDetails $details): EvaluationDetails
    {
        return (new EvaluationDetailsBuilder())
                    ->withFlagKey($flagKey)
                    ->withValue($details->getValue())
                    ->withError($details->getError())
                    ->withReason($details->getReason())
                    ->withVariant($details->getVariant())
                    ->build();
    }
}
