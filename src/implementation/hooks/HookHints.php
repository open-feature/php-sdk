<?php

declare(strict_types=1);

namespace OpenFeature\implementation\hooks;

use DateTime;
use OpenFeature\interfaces\hooks\HookHints as HookHintsInterface;

use function array_keys;

class HookHints implements HookHintsInterface
{
    /** @var Array<string, bool | string | float | int | DateTime | mixed[] | null> $hints */
    private array $hints = [];

    /**
     * @return bool | string | float | int | DateTime | mixed[] | null
     */
    public function get(string $key)
    {
        return $this->hints[$key];
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->hints);
    }

    /**
     * @param Array<string, bool | string | float | int | DateTime | mixed[] | null> $hints
     */
    public function __construct(array $hints = [])
    {
        $this->hints = $hints;
    }
}
