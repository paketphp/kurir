<?php
declare(strict_types=1);

namespace Paket\Kurir;

use LogicException;
use Paket\Kurir\Fixture\OtherTestEvent;
use Paket\Kurir\Fixture\Subscriber;
use Paket\Kurir\Fixture\SubscriberWithUnionEventListener;
use Paket\Kurir\Fixture\TestClass;
use Paket\Kurir\Fixture\TestEvent;
use PHPUnit\Framework\TestCase;

final class KurirTest extends TestCase
{
    /** @var Kurir */
    private $kurir;

    public function setUp(): void
    {
        $this->kurir = new Kurir();
    }

    public function testThatEmittingWithoutSubscribersDoesNothing()
    {
        $this->kurir->emit(new TestEvent());
        $this->assertTrue(true);
    }

    public function testThatSubscribedListenerGetsEvent()
    {
        $this->kurir->subscribe(function (TestEvent $event) {
            $this->assertTrue(true);
        });

        $this->kurir->emit(new TestEvent());
    }

    public function testThatListenerGetCorrectEvent()
    {
        $test = new TestEvent();
        $this->kurir->subscribe(function (TestEvent $event) use ($test) {
            $this->assertSame($test, $event);
        });

        $this->kurir->emit($test);
    }

    public function testThatSubscribedClassStaticListenerGetsEvent()
    {
        $this->kurir->subscribe([Subscriber::class, 'staticListener']);

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribedClassStaticStringListenerGetsEvent()
    {
        $this->kurir->subscribe('\Paket\Kurir\Fixture\Subscriber::staticListener');

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribedInstanceStaticListenerGetsEvent()
    {
        $subscriber = new Subscriber();
        $this->kurir->subscribe([$subscriber, 'staticListener']);

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribedInstanceListenerGetsEvent()
    {
        $subscriber = new Subscriber();
        $this->kurir->subscribe([$subscriber, 'listener']);

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribedInstanceInvokeListenerGetsEvent()
    {
        $subscriber = new Subscriber();
        $this->kurir->subscribe($subscriber);

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribedFunctionGetsEvent()
    {
        require __DIR__ . '/Fixture/fnsubscriber.php';

        $this->kurir->subscribe('\Paket\Kurir\Fixture\subscriber');

        $this->kurir->emit(new TestEvent());
    }

    public function testThatSubscribingTwiceWithTheSameListenerOnlyGetsOneEvent()
    {
        $subscriber = new Subscriber();
        $this->kurir->subscribe([$subscriber, 'onceListener']);
        $this->kurir->subscribe([$subscriber, 'onceListener']);

        $this->kurir->emit(new TestEvent());
    }

    public function testThatMultipleSubscribersAllGetsEvent()
    {
        $counter = 0;

        $this->kurir->subscribe(function (TestEvent $event) use (&$counter) {
            $counter++;
        });

        $this->kurir->subscribe(function (TestEvent $event) use (&$counter) {
            $counter++;
        });

        $this->kurir->emit(new TestEvent());
        $this->assertSame(2, $counter);
    }

    public function testThatCorrectEventGoesToCorrectSubscriber()
    {
        $this->kurir->subscribe(function (TestEvent $event) {
            $this->assertTrue(true);
        });

        $this->kurir->subscribe(function (OtherTestEvent $event) {
            $this->fail();
        });

        $this->kurir->emit(new TestEvent());
    }

    public function testThatUnSubscribedListenerDoesNotGetEvent()
    {
        $listener = $this->kurir->subscribe(function (TestEvent $event) {
            $this->fail();
        });

        $this->kurir->unsubscribe($listener);

        $this->kurir->emit(new TestEvent());
        $this->assertTrue(true);
    }

    public function testThatSubscribeReturnsTheSameCallable()
    {
        $callable = function (TestEvent $event) {
        };

        $this->assertSame($callable, $this->kurir->subscribe($callable));
    }

    public function testThatUnsubscribingWithNonSubscribedListenerDoesNothing()
    {
        $callable = function (TestEvent $event) {
        };

        $this->kurir->unsubscribe($callable);
        $this->assertTrue(true);
    }

    public function testThatSubscribingWithoutEventThrows()
    {
        $this->expectException(LogicException::class);
        $this->kurir->subscribe(function () {
        });
    }

    public function testThatSubscribingWithoutTypeThrows()
    {
        $this->expectException(LogicException::class);
        $this->kurir->subscribe(function ($event) {
        });
    }

    public function testThatSubscribingToMultipleEventsThrows()
    {
        $this->expectException(LogicException::class);
        $this->kurir->subscribe(function (TestEvent $event, OtherTestEvent $other) {
        });
    }

    public function testThatSubscribingToNonEventThrows()
    {
        $this->expectException(LogicException::class);
        $this->kurir->subscribe(function (TestClass $event) {
        });
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testThatSubscribingToUnionOfEventsThrows()
    {
        $this->expectException(LogicException::class);
        $this->kurir->subscribe([SubscriberWithUnionEventListener::class, 'listener']);
    }
}