<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use OpenFeature\interfaces\common\Metadata as MetadataInterface;

class Metadata implements MetadataInterface
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
