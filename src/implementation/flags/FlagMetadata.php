<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\implementation\common\ArrayHelper;
use OpenFeature\interfaces\flags\FlagMetadata as FlagMetadataInterface;

class FlagMetadata implements FlagMetadataInterface
{
    /**
     * @param Array<array-key, bool|string|int|float> $metadata
     */
    public function __construct(protected array $metadata = [])
    {
    }

    /**
     * Return key-type pairs of the attributes
     *
     * @return Array<int, string>
     */
    public function keys(): array
    {
        return ArrayHelper::getStringKeys($this->metadata);
    }

    public function get(string $key): bool | string | int | float | null
    {
        return $this->metadata[$key] ?? null;
    }

    /**
     * @return Array<array-key, bool|string|int|float>
     */
    public function toArray(): array
    {
        return [...$this->metadata];
    }
}
