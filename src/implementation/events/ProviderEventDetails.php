<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

use OpenFeature\interfaces\events\ProviderEventDetails as ProviderEventDetailsInterface;
use OpenFeature\interfaces\provider\ErrorCode;

class ProviderEventDetails implements ProviderEventDetailsInterface
{
    private ?string $message;

    /** @var string[] */
    private array $flagsChanged;

    /** @var array<string, mixed> */
    private array $eventMetadata;
    private ?ErrorCode $errorCode;

    /**
     * @param string[] $flagsChanged
     * @param array<string, mixed> $eventMetadata
     */
    public function __construct(
        ?string $message = null,
        array $flagsChanged = [],
        array $eventMetadata = [],
        ?ErrorCode $errorCode = null,
    ) {
        $this->message = $message;
        $this->flagsChanged = $flagsChanged;
        $this->eventMetadata = $eventMetadata;
        $this->errorCode = $errorCode;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /** @return string[] */
    public function getFlagsChanged(): array
    {
        return $this->flagsChanged;
    }

    /** @return array<string, mixed> */
    public function getEventMetadata(): array
    {
        return $this->eventMetadata;
    }

    public function getErrorCode(): ?ErrorCode
    {
        return $this->errorCode;
    }
}
