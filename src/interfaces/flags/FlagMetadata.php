<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

/**
 * flag metadata MUST be a structure supporting the definition of arbitrary properties,
 * with keys of type string, and values of type boolean | string | number.
 */
interface FlagMetadata
{
    /**
     * Return key-type pairs of the attributes
     *
     * @return array<int, string>
     */
    public function keys(): array;

    public function get(string $key): bool | string | int | float | null;

    /**
     * @return array<array-key, bool|string|int|float|null>
     */
    public function toArray(): array;
}
