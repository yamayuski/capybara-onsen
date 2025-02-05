<?php

declare(strict_types=1);

/**
 * Class AsChronosTraitTest
 * @package CapybaraOnsen\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace CapybaraOnsen\Clock;

use Cake\Chronos\Chronos;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

#[CoversClass(AsChronosTrait::class)]
final class AsChronosTraitTest extends TestCase
{
    #[Test]
    public function testAsChronos(): void
    {
        $clock = new class implements ClockInterface
        {
            use AsChronosTrait;

            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        self::assertInstanceOf(Chronos::class, $clock->asChronos());
        self::assertSame("2021-01-01 00:00:00", $clock->asChronos()->format("Y-m-d H:i:s"));
    }
}
