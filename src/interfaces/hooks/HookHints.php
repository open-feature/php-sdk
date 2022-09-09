<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\hooks;

use DateTime;
use OpenFeature\interfaces\common\StringIndexed;

/**
 * -----------------
 * Requirement 4.2.1
 * -----------------
 * hook hints MUST be a structure supports definition of arbitrary properties, with keys
 * of type string, and values of type boolean | string | number | datetime | structure
 */
interface HookHints extends StringIndexed
{
    /**
     * @return bool | string | float | int | DateTime | mixed[] | null
     */
    public function get(string $key);
}
