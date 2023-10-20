<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Time;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class Clock implements ClockInterface
{
    private static ?DateTimeImmutable $now = null;
    private static ?DateTimeImmutable $traveledAt = null;

    public function now(): DateTimeImmutable
    {
        if (null === self::$now) {
            return new DateTimeImmutable();
        }

        if (null === self::$traveledAt) {
            return clone self::$now;
        }

        // TODO this works reliably only for short periods like under 60 seconds
        $interval = self::$traveledAt->diff(new DateTimeImmutable());
        return self::$now->add($interval);
    }

    public function freeze(DateTimeImmutable $time): void
    {
        self::$now = $time;
        self::$traveledAt = null;
    }

    public function return(): void
    {
        self::$now = null;
        self::$traveledAt = null;
    }
}
