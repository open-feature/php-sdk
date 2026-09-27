<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

use OpenFeature\interfaces\provider\ErrorCode;

interface ProviderEventDetails
{
    public function getMessage(): ?string;

    /** @return string[] */
    public function getFlagsChanged(): array;

    /** @return array<string, mixed> */
    public function getEventMetadata(): array;

    public function getErrorCode(): ?ErrorCode;
}
