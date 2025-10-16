<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider;

use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;
use Throwable;

/**
 * Represents the result of evaluating a single provider in a multi-provider setup.
 * Contains the resolution details, any error that was thrown, and the source provider information.
 */
class ProviderResolutionResult
{
    public function __construct(
        private string $providerName,
        private Provider $provider,
        private ?ResolutionDetails $details = null,
        private ?Throwable $error = null,
    ) {
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getDetails(): ?ResolutionDetails
    {
        return $this->details;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function isSuccessful(): bool
    {
        return $this->details !== null && $this->error === null;
    }
}
