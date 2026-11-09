<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use DateTime;
use OpenFeature\interfaces\flags\Attributes as AttributesInterface;
use OpenFeature\interfaces\flags\MutableAttributes as MutableAttributesInterface;

class MutableAttributes extends Attributes implements MutableAttributesInterface
{
    public static function from(AttributesInterface $attributes): MutableAttributes
    {
        $attributeMap = [];
        foreach ($attributes->keys() as $key) {
            $attributeMap[$key] = $attributes->get($key);
        }

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
