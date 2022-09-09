<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

use DateTime;

interface TypeValuePair
{
    public function getType(): string;

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getValue();
}
