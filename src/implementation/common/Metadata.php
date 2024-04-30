<?php

declare(strict_types=1);

namespace OpenFeature\implementation\common;

use OpenFeature\interfaces\common\Metadata as MetadataInterface;

class Metadata implements MetadataInterface
{
    public function __construct(private string $domain)
    {
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @deprecated Use getDomain
     */
    public function getName(): string
    {
        return $this->domain;
    }

    public function setName(string $name): void
    {
        $this->domain = $name;
    }
}
