<?php

interface EventListenerInterface
{
    public function handle(object $event): void;
}