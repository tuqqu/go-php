<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

final class CapGrowthHelper
{
    private const int THRESHOLD = 256;

    private function __construct() {}

    public static function calculateCap(int $cap): int
    {
        if ($cap === 0) {
            return 1;
        }

        if ($cap < self::THRESHOLD) {
            return $cap << 1;
        }

        return ($cap << 1) + (int) (($cap + 3 * self::THRESHOLD) / 4);
    }
}
