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

    public function now(): DateTimeImmutable
    {
        if (null === self::$now) {
            return new DateTimeImmutable();
        }

        return clone self::$now;
    }

    public function freeze(DateTimeImmutable $time): void
    {
        self::$now = $time;
    }

    public function return(): void
    {
        self::$now = null;
    }
}
