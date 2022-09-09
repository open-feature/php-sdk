<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use DateTime;
use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;

class ResolutionDetailsFactory
{
    /**
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public static function fromSuccess($value): ResolutionDetailsInterface
    {
        return (new ResolutionDetailsBuilder())
                    ->withValue($value)
                    ->build();
    }
}
