<?php

class NoListenerRegisteredException extends \InvalidArgumentException
{
    public function __construct(
        public readonly string $eventName,
        public readonly object $event
    )
    {
        parent::__construct(
            "No listener registered for this event"
        );
    }
}