<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface EventMetadata
{
    public function has(string $key): bool;

    public function get(string $key): bool | string | int | float | null;

    /**
     * @return string[]
     */
    public function keys(): array;

    /**
     * @return Array<array-key, bool|string|int|float>
     */
    public function toArray(): array;
}
