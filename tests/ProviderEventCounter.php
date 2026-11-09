<?php

declare(strict_types=1);

namespace OpenFeature\Test;

final class ProviderEventCounter
{
    private int $value = 0;

    public function increment(): void
    {
        ++$this->value;
    }

    public function reset(): void
    {
        $this->value = 0;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
