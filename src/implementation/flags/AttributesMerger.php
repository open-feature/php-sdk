<?php

declare(strict_types=1);

namespace OpenFeature\implementation\flags;

use OpenFeature\interfaces\flags\Attributes as AttributesInterface;

use function array_shift;
use function sizeof;

class AttributesMerger
{
    /**
     * Merges an Attributes object into another Attributes
     */
    public static function merge(AttributesInterface ...$attributes): AttributesInterface
    {
        if (sizeof($attributes) === 0) {
            return new Attributes();
        }

        $initialAttributes = array_shift($attributes);

        if ($initialAttributes instanceof MutableAttributes) {
            $mutableAttributes = $initialAttributes;
        } else {
            $mutableAttributes = new MutableAttributes();
            $mutableAttributes->mergeWith($initialAttributes);
        }

        foreach ($attributes as $additionalAttributes) {
            $mutableAttributes->mergeWith($additionalAttributes);
        }

        return $mutableAttributes;
    }
}
