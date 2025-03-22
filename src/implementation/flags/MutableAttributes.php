<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\interfaces\flags\Attributes as AttributesInterface;
use OpenFeature\interfaces\flags\MutableAttributes as MutableAttributesInterface;

use function array_reduce;

class MutableAttributes extends Attributes implements MutableAttributesInterface
{
    public static function from(AttributesInterface $attributes): MutableAttributes
    {
        $attributeMap = array_reduce(
            $attributes->keys(),
            /**
             * @param array<string, bool|string|int|float|DateTime|mixed[]|null> $map
             *
             * @return array<string, bool|string|int|float|DateTime|mixed[]|null>
             */
            static function (array $map, string $key) use ($attributes) {
                $map[$key] = $attributes->get($key);

                return $map;
            },
            [],
        );

        return new MutableAttributes($attributeMap);
    }

    public function add(string $key, bool | string | int | float | DateTime | array | null $value): void
    {
        $this->attributesMap[$key] = $value;
    }

    /**
     * Merges an Attributes object into another Attributes
     */
    public function mergeWith(AttributesInterface $additionalAttributes): AttributesInterface
    {
        $mutableAttributes = $this;

        foreach ($additionalAttributes->keys() as $key) {
            $mutableAttributes->add($key, $additionalAttributes->get($key));
        }

        return $mutableAttributes;
    }
}
