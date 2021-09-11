<?php
declare(strict_types=1);

namespace Paket\Kurir\Fixture;

use PHPUnit\Framework\Assert;

final class Subscriber
{
    private static $counter = 0;

    public static function staticListener(TestEvent $event): void
    {
        Assert::assertTrue(true);
    }

    public static function listener(TestEvent $event): void
    {
        Assert::assertTrue(true);
    }

    public function __invoke(TestEvent $event)
    {
        Assert::assertTrue(true);
    }

    public static function onceListener(TestEvent $event): void
    {
        self::$counter++;
        if (self::$counter > 1) {
            Assert::fail();
        }
        Assert::assertTrue(true);
    }
}