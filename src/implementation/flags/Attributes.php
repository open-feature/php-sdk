<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\implementation\common\ArrayHelper;
use OpenFeature\interfaces\flags\Attributes as AttributesInterface;

class Attributes implements AttributesInterface
{
    /**
     * @param array<array-key, bool|string|int|float|DateTime|mixed[]|null> $attributesMap
     */
    public function __construct(protected array $attributesMap = [])
    {
    }

    /**
     * Return key-type pairs of the attributes
     *
     * @return array<int, string>
     */
    public function keys(): array
    {
        return ArrayHelper::getStringKeys($this->attributesMap);
    }

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function get(string $key): bool | string | int | float | DateTime | array | null
    {
        return $this->attributesMap[$key] ?? null;
    }

    /**
     * @return array<array-key, bool|string|int|float|DateTime|mixed[]|null>
     */
    public function toArray(): array
    {
        return [...$this->attributesMap];
    }
}
