<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events;

use OpenFeature\implementation\common\ArrayHelper;
use OpenFeature\interfaces\events\EventMetadata as EventMetadataInterface;

use function array_merge;
use function key_exists;

class EventMetadata implements EventMetadataInterface
{
    /**
     * @param array<string, bool|string|int|float> $eventMetadataMap
     */
    public function __construct(protected array $eventMetadataMap = [])
    {
    }

    public function has(string $key): bool
    {
        return key_exists($key, $this->eventMetadataMap);
    }

    /**
     * Return key-type pairs of the EventMetadata
     *
     * @return array<int, string>
     */
    public function keys(): array
    {
        return ArrayHelper::getStringKeys($this->eventMetadataMap);
    }

    public function get(string $key): bool | string | int | float | null
    {
        if ($this->has($key)) {
            return $this->eventMetadataMap[$key];
        }

        return null;
    }

    /**
     * @return array<string, bool|string|int|float>
     */
    public function toArray(): array
    {
        return array_merge([], $this->eventMetadataMap);
    }
}
