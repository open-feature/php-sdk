<?php

declare(strict_types=1);

namespace OpenFeature\interfaces\events;

interface EventDetails
{
    public function getClientName(): string;

  /**
   * @return Array<string>
   */
    public function getChangedFlags(): array;

    public function getMessage(): ?string;

    public function getEventMetadata(): EventMetadata;
}
