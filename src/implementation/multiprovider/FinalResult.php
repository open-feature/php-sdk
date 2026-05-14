<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider;

use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

use function count;

/**
 * Represents the final result from a multi-provider evaluation strategy.
 * Contains either successful resolution details or aggregated errors.
 */
class FinalResult
{
    /**
     * @param ResolutionDetails|null $details The final resolution details if successful
     * @param string|null $providerName The name of the provider that provided the final result
     * @param array<int, array{providerName: string, error: Throwable}>|null $errors Array of errors from providers if unsuccessful
     */
    public function __construct(
        private ?ResolutionDetails $details = null,
        private ?string $providerName = null,
        private ?array $errors = null,
    ) {
    }

    public function getDetails(): ?ResolutionDetails
    {
        return $this->details;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @return array<int, array{providerName: string, error: Throwable}>|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function isSuccessful(): bool
    {
        return $this->details !== null && $this->errors === null;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== null && count($this->errors) > 0;
    }
}
