<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\common;

interface MetadataGetter
{
    /**
     * Returns the metadata for the current resource
     */
    public function getMetadata(): Metadata;
}
