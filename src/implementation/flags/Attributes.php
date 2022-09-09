<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\implementation\common\ArrayHelper;
use OpenFeature\interfaces\flags\Attributes as AttributesInterface;

use function array_merge;

class Attributes implements AttributesInterface
{
    /** @var Array<array-key, bool|string|int|float|DateTime|mixed[]|null> $attributesMap */
    protected array $attributesMap;

    /**
     * @param Array<array-key, bool|string|int|float|DateTime|mixed[]|null> $attributesMap
     */
    public function __construct(array $attributesMap = [])
    {
        $this->attributesMap = $attributesMap;
    }

    /**
     * Return key-type pairs of the attributes
     *
     * @return Array<int, string>
     */
    public function keys(): array
    {
        return ArrayHelper::getStringKeys($this->attributesMap);
    }

    /**
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function get(string $key)
    {
        if (isset($this->attributesMap[$key])) {
            return $this->attributesMap[$key];
        }

        return null;
    }

    /**
     * @return Array<array-key, bool|string|int|float|DateTime|mixed[]|null>
     */
    public function toArray(): array
    {
        return array_merge([], $this->attributesMap);
    }
}
