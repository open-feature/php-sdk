<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\provider;

/**
 * ---------------
 * Requirement 2.8
 * ---------------
 * In cases of abnormal execution, the provider MUST indicate an error using the idioms of the implementation
 * language, with an associated error code and optional associated error message.
 */
interface ResolutionError
{
    /**
     * ---------------
     * Requirement 2.8
     * ---------------
     * In cases of abnormal execution, the provider MUST indicate an error using the idioms of the implementation
     * language, with an associated error code and optional associated error message.
     */
    public function getCode(): ErrorCode;

    /**
     * ---------------
     * Requirement 2.8
     * ---------------
     * In cases of abnormal execution, the provider MUST indicate an error using the idioms of the implementation
     * language, with an associated error code and optional associated error message.
     */
    public function getMessage(): ?string;
}
