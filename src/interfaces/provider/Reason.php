<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * Resolution reason
 */
final class Reason
{
    /**
     * The resolved value was configured statically, or otherwise fell back to a pre-configured value.
     */
    public const DEFAULT = 'DEFAULT';

    /**
     * The resolved value was the result of a dynamic evaluation, such as a rule or specific user-targeting.
     */
    public const TARGETING_MATCH = 'TARGETING_MATCH';

    /**
     * The resolved value was the result of pseudorandom assignment.
     */
    public const SPLIT = 'SPLIT';

    /**
     * The resolved value was the result of the flag being disabled in the management system.
     */
    public const DISABLED = 'DISABLED';

    /**
     * The reason for the resolved value could not be determined.
     */
    public const UNKNOWN = 'UNKNOWN';

    /**
     * The resolved value was the result of an error.
     */
    public const ERROR = 'ERROR';
}
