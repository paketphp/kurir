<?php
declare(strict_types=1);

namespace Paket\Kurir\Fixture;

final class SubscriberWithUnionEventListener
{
    public static function listener(TestEvent|OtherTestEvent $event): void
    {

    }
}