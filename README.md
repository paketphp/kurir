# Kurir

Kurir (_Swedish_ courier) is a minimal event system for PHP.  With Kurir you can subscribe to custom typed events.

![](https://github.com/paketphp/kurir/workflows/tests/badge.svg)

## Installation

`composer require paket/kurir`

Requires PHP 7.1 or higher.

## Usage

```
final class MyEvent implements Event
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

$kurir = new Kurir();

$kurir->subscribe(function (MyEvent $event) {
   echo $event->getMessage();
});

$kurir->emit(new MyEvent('Hello, World!'));
```

## General

### Core interfaces

Kurir consist of three interfaces, `Event`, `EventSource` and  `EventEmitter`, where
class `Kurir` implements `EventSource` and `EventEmitter`.

#### Event

```
interface Event
{
}
```

`Event` is the common type of all events, but it doesn't provide any methods, it is up to each event implementation to add the methods or properties that it needs.

#### EventSource

```
interface EventSource
{
    public function subscribe(callable $listener): callable;

    public function unsubscribe(callable $listener): void;
}
```

Listeners can subscribe and unsubscribe to events by using `EventSource`. `subsribe()` returns the same callable that is past as a parameter to it, this helps when unsubscribing.

```
$listener = $eventSource->subscribe(function (MyEvent $event) {
   echo $event->getMessage();
});

$eventSource->unsubscribe($listener);
```

#### EventEmitter

```
interface EventEmitter
{
   public function emit(Event $event): void;
}
```

Used to emit one event of type `Event` to every subscriber of that event type. 

### Example

By separating  `EventSource` and `EventEmitter` to two different interfaces you only need to expose one or the other in program code, e.g. lets say we have a repository that saves records and we want to log that.

```
final class InsertedRecordEvent implements Event
{
    private $id;
    private $record;

    public function __construct(int $id, array $record)
    {
        $this->id = $id;
        $this->record = $record;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRecord(): array
    {
        return $this->record;
    }
}

interface Storage
{
    public function insert(string $location, array $record): int;
}

final class RecordRepository
{
    private $storage;
    private $eventEmitter;

    public function __construct(Storage $storage, EventEmitter $eventEmitter)
    {
        $this->storage = $storage;
        $this->eventEmitter = $eventEmitter;
    }

    public function insertRecord(array $record)
    {
        $id = $this->storage->insert('record', $record);
        $this->eventEmitter->emit(new InsertedRecordEvent($id, $record));
    }
}

final class RecordLog
{
    private $eventSource;
    private $listener;

    public function __construct(EventSource $eventSource)
    {
        $this->eventSource = $eventSource;
        $this->listener = $this->eventSource->subscribe(function (InsertedRecordEvent $event) {
            echo "Inserted record {$event->getId()}";
        });
    }

    public function __destruct()
    {
        $this->eventSource->unsubscribe($this->listener);
    }
}


$kurir = new Kurir();
$repository = new RecordRepository($storage, $kurir);
$log = new RecordLog($kurir);
$repository->insertRecord(['action' => 'login', 'user' => 'joe', 'time' => 1631375296]);
```


