<?php
declare(strict_types=1);

namespace Paket\Kurir\Fixture;

use PHPUnit\Framework\Assert;

function subscriber(TestEvent $event): void
{
    Assert::assertTrue(true);
}
