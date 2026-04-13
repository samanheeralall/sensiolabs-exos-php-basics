<?php

require_once __DIR__.'/EventDispatcher.php';

$eventDispatcher = new EventDispatcher();
$eventDispatcher->addListener('event_foo', function($event) {
    echo 'event_foo dispatched';
});
$eventDispatcher->dispatch(new stdClass(), 'event_foo');

