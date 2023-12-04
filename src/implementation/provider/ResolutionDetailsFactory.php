<?php

declare(strict_types=1);

namespace OpenFeature\implementation\provider;

use OpenFeature\interfaces\provider\ResolutionDetails as ResolutionDetailsInterface;

class ResolutionDetailsFactory
{
    /**
     * @param bool|string|int|float|mixed[]|null $value
     */
    public static function fromSuccess(bool | string | int | float | array | null $value): ResolutionDetailsInterface
    {
        return (new ResolutionDetailsBuilder())
                    ->withValue($value)
                    ->build();
    }
}
