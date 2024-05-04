<?php

declare(strict_types=1);

namespace OpenFeature\implementation\events\listener;

use League\Event\HasEventName;
use League\Event\PrioritizedListenersForEvent;
use OpenFeature\interfaces\events\Priority;
use OpenFeature\interfaces\events\ProviderEvent;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerRegistry implements ListenerProviderInterface
{
    /** @var array<string,ListenersForEvent> */
    protected $listenersPerEvent = [];

    public function subscribeTo(ProviderEvent $event, callable $listener, Priority $priority): void
    {
        $group = array_key_exists($event, $this->listenersPerEvent)
            ? $this->listenersPerEvent[$event]
            : $this->listenersPerEvent[$event] = new ListenersForEvent();

        $listener = $listener instanceof IdentifiableListener ? $listener : new MultiCallListener($listener);

        $group->addListener($listener, $priority->value);
    }

    public function subscribeOnceTo(ProviderEvent $event, callable $listener, Priority $priority): void
    {
        $this->subscribeTo($event, new SingleCallListener($listener), $priority);
    }

    public function unsubscribeFrom(ProviderEvent $event, callable $listener): void
    {
        if (!array_key_exists($event, $this->listenersPerEvent)) {
            return;
        }

        $listener = $listener instanceof IdentifiableListener ? $listener : new MultiCallListener($listener);

        $group = $this->listenersPerEvent[$event];

        $group->removeListener($listener);
    }

    public function getListenersForEvent(object $event): iterable
    {
        /**
         * @var string                       $key
         * @var PrioritizedListenersForEvent $group
         */
        foreach ($this->listenersPerEvent as $key => $group) {
            if ($event instanceof $key) {
                yield from $group->getListeners();
            }
        }

        if ($event instanceof HasEventName) {
            yield from $this->getListenersForEventName($event->eventName());
        }
    }

    private function getListenersForEventName(string $eventName): iterable
    {
        if ( ! array_key_exists($eventName, $this->listenersPerEvent)) {
            return [];
        }

        return $this->listenersPerEvent[$eventName]->getListeners();
    }

    // public function subscribeListenersFrom(ListenerSubscriber $subscriber): void
    // {
    //     $subscriber->subscribeListeners($this);
    // }
}