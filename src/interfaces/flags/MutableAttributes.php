<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use DateTime;

interface MutableAttributes extends Attributes
{
    /**
     * Adds a value to the attributes
     *
     * @param bool|string|int|float|DateTime|mixed[]|null $value
     */
    public function add(string $key, $value): void;
}
