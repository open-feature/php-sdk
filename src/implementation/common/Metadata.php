<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use OpenFeature\interfaces\common\Metadata as MetadataInterface;

class Metadata implements MetadataInterface
{
    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
