<?php
declare(strict_types=1);

namespace Paket\Kurir;

interface EventEmitter
{
    /**
     * Emit event to every subscriber of the implemented Event type
     *
     * @param Event $event
     */
    public function emit(Event $event): void;
}