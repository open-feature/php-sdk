<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events\listener;

use function krsort;
use const SORT_NUMERIC;

class ListenersForEvent
{
    /** @var array<int, array<int,callable>> */
    private $listeners = [];
    /** @var array<int, array<string,{0: int, 1: int}>> */
    private $listenerIndexMap = [];
    /** @var array<int,callable>|null */
    private $sortedListeners;

    public function addListener(IdentifiableListener $listener, int $priority): void
    {
        $this->sortedListeners = null;
        $listenerIndex = count($this->listeners);
        $this->listeners[$priority][] = $listener;
        $id = strval($listener->getId());
        $this->listenerIndexMap[$priority][$id] = $listenerIndex;
    }

    public function removeListener(IdentifiableListener $listener): void
    {
        $this->sortedListeners = null;
        $id = $listener->getId();
        [$priority, $listenerIndex] = $this->listenerIndexMap[$id];
        unset($this->listenerIndexMap[$id]);
        array_splice($this->listeners[$priority], $listenerIndex, 1);
    }

    public function getListeners(): iterable
    {
        return $this->sortedListeners ?? $this->sortListeners();
    }

    private function sortListeners(): array
    {
        $listeners = [];
        krsort($this->listeners, SORT_NUMERIC);
        $filter = static function ($listener): bool {
            return $listener instanceof SingleCallListener === false;
        };

        foreach ($this->listeners as $priority => $group) {
            foreach ($group as $listener) {
                $listeners[] = $listener;
            }
            $this->listeners[$priority] = array_filter($group, $filter);
        }

        $this->sortedListeners = array_filter($listeners, $filter);

        return $listeners;
    }
}
