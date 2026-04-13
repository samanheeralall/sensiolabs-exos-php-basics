<?php

require_once __DIR__.'/EventListenerInterface.php';
require_once __DIR__.'/EventDispatcher.php';

$dispatcher = new EventDispatcher();

// Listener avec callable (exercice 1)
$dispatcher->addListener(eventName: 'event_foo', listener: function($event) {
    echo 'Event Foo dispatched!'.PHP_EOL;
});

// Listener avec interface (exercice 2) — classe anonyme
$dispatcher->addListener(eventName: 'event_foo', listener: new class implements EventListenerInterface {
    public function handle(object $event): void
    {
        echo 'Event Foo listened from Interface'.PHP_EOL;
    }
});

$dispatcher->dispatch(new stdClass(), eventName: 'event_foo');