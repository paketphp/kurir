<?php
declare(strict_types=1);

namespace Paket\Kurir;

interface EventSource
{
    /**
     * Subscribe to the implemented Event described by the first parameter of the listener
     * Returns the same callable for better ergonomics when unsubscribing.
     *
     * @param callable $listener
     * @return callable
     */
    public function subscribe(callable $listener): callable;

    /**
     * Unsubscribe listener to previously subscribed event
     *
     * @param callable $listener
     */
    public function unsubscribe(callable $listener): void;
}