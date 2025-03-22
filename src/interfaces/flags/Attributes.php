<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use DateTime;

interface Attributes
{
    /**
     * Return key-type pairs of the attributes
     *
     * @return array<int, string>
     */
    public function keys(): array;

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function get(string $key): bool | string | int | float | DateTime | array | null;

    /**
     * @return array<array-key, bool|string|int|float|DateTime|mixed[]|null>
     */
    public function toArray(): array;
}
