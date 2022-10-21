<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

use DateTime;

/**
 * A structure which contains a subset of the fields defined in the evaluation
 * details, representing the result of the provider's flag resolution process
 *
 * @see https://docs.openfeature.dev/docs/specification/glossary#resolving-flag-values
 */
interface ResolutionDetails
{
    /**
     * ---------------
     * Requirement 2.4
     * ---------------
     * In cases of normal execution, the provider MUST populate the flag resolution structure's value field with
     * the resolved flag value.
     *
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getValue();

    /**
     * ---------------
     * Requirement 2.8
     * ---------------
     * In cases of abnormal execution, the provider MUST indicate an error using the idioms of the implementation
     * language, with an associated error code and optional associated error message.
     */
    public function getError(): ?ResolutionError;

    /**
     * ---------------
     * Requirement 2.6
     * ---------------
     * The provider SHOULD populate the flag resolution structure's reason field with a string indicating the
     * semantic reason for the returned flag value.
     */
    public function getReason(): ?string;

    /**
     * ---------------
     * Requirement 2.5
     * ---------------
     * In cases of normal execution, the provider SHOULD populate the flag resolution structure's variant field
     * with a string identifier corresponding to the returned flag value.
     */
    public function getVariant(): ?string;
}
