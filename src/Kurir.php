<?php
declare(strict_types=1);

namespace Paket\Kurir;

use Closure;
use LogicException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

final class Kurir implements EventEmitter, EventSource
{
    /** @var array */
    private $listeners = [];

    public function emit(Event $event): void
    {
        $name = get_class($event);
        foreach ($this->listeners[$name] ?? [] as $listener) {
            $listener($event);
        }
    }

    public function subscribe(callable $listener): callable
    {
        $name = $this->getEventName($listener);
        $hash = $this->getCallableHash($listener);
        $this->listeners[$name][$hash] = $listener;
        return $listener;
    }

    public function unsubscribe(callable $listener): void
    {
        $name = $this->getEventName($listener);
        $hash = $this->getCallableHash($listener);
        unset($this->listeners[$name][$hash]);
    }

    private function getEventName(callable $listener): string
    {
        try {
            if ($listener instanceof Closure) {
                $rf = new ReflectionFunction($listener);
            } elseif (is_array($listener)) {
                $rf = new ReflectionMethod($listener[0], $listener[1]);
            } elseif (is_object($listener)) {
                $rf = new ReflectionMethod($listener, '__invoke');
            } elseif (is_string($listener)) {
                $parts = explode('::', $listener, 2);
                if (isset($parts[1])) {
                    $rf = new ReflectionMethod($parts[0], $parts[1]);
                } else {
                    $rf = new ReflectionFunction($listener);
                }
            } else {
                throw new LogicException('Unknown callable type');
            }
        } catch (ReflectionException $e) {
            throw new LogicException('Failed reflecting listener', 0, $e);
        }

        $parameters = $rf->getParameters();
        $count = count($parameters);
        if ($count !== 1) {
            throw new LogicException("Listener only allows one parameter not {$count}");
        }
        $rp = $parameters[0];
        $type = $rp->getType();
        if ($type === null) {
            throw new LogicException("Missing type for parameter {$rp->getName()}");
        }
        if (!($type instanceof ReflectionNamedType)) {
            throw new LogicException("Union types are not supported for parameter {$rp->getName()}");
        }
        $name = $type->getName();
        if (!in_array(Event::class, class_implements($name), true)) {
            throw new LogicException("Parameter {$rp->getName()} must implement Event interface");
        }
        return $name;
    }

    public function getCallableHash(callable $callable): string
    {
        if (is_object($callable)) {
            return spl_object_hash($callable);
        }

        if (is_array($callable)) {
            if (is_object($callable[0])) {
                return spl_object_hash($callable[0]) . '::' . $callable[1];
            }
            return "{$callable[0]}::{$callable[1]}";
        }

        if (is_string($callable)) {
            return $callable;
        }
        throw new LogicException('Unknown hash for callable');
    }
}