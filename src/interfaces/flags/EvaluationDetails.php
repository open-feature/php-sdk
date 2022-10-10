<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\flags;

use DateTime;
use OpenFeature\interfaces\provider\ErrorCode;

/**
 * A structure representing the result of the flag evaluation process,
 * and made available in the detailed flag resolution functions
 *
 * @see https://docs.openfeature.dev/docs/specification/glossary#evaluating-flag-values
 * @see https://docs.openfeature.dev/docs/specification/sections/flag-evaluation#detailed-flag-evaluation
 */
interface EvaluationDetails
{
    /**
     * -----------------
     * Requirement 1.4.4
     * -----------------
     * The evaluation details structure's flag key field MUST contain the flag key argument
     * passed to the detailed flag evaluation method.
     */
    public function getFlagKey(): string;

    /**
     * -----------------
     * Requirement 1.4.2
     * -----------------
     * The evaluation details structure's value field MUST contain the evaluated flag value.
     *
     * @return bool|string|int|float|DateTime|mixed[]|null
     */
    public function getValue();

    /**
     * -----------------
     * Requirement 1.4.7
     * -----------------
     * In cases of abnormal execution, the evaluation details structure's error code field
     * MUST contain a string identifying an error occurred during flag evaluation and the
     * nature of the error.
     */
    public function getErrorCode(): ?ErrorCode;

    /**
     * -----------------
     * Requirement 1.4.6
     * -----------------
     * In cases of normal execution, the evaluation details structure's reason field MUST
     * contain the value of the reason field in the flag resolution structure returned by
     * the configured provider, if the field is set.
     */
    public function getReason(): ?string;

    /**
     * -----------------
     * Requirement 1.4.5
     * -----------------
     * In cases of normal execution, the evaluation details structure's variant field MUST
     * contain the value of the variant field in the flag resolution structure returned by
     * the configured provider, if the field is set.
     */
    public function getVariant(): ?string;
}
