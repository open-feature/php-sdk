<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

interface AttributeByTypeExporter
{
    /**
     * Retrieves a subset of the Attributes structure by whether the
     * value is a structure type
     */
    public function getStructureAttributes(): Attributes;

    /**
     * Retrieves a subset of the Attributes structure by whether the
     * value is a string type
     */
    public function getStringAttributes(): Attributes;

    /**
     * Retrieves a subset of the Attributes structure by whether the
     * value is a integer type
     */
    public function getIntegerAttributes(): Attributes;

    /**
     * Retrieves a subset of the Attributes structure by whether the
     * value is a float type
     */
    public function getFloatAttributes(): Attributes;

    /**
     * Retrieves a subset of the Attributes structure by whether the
     * value is a boolean type
     */
    public function getBooleanAttributes(): Attributes;
}
