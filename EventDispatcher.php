<?php

class EventDispatcher
{
    private array $listeners = [];
    public function addListener(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(object $event, string $eventName) : object
    {
        if (!isset($this->listeners[$eventName])) {
            return $event;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            $listener($event);
        }

        return $event;
    }
}