<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoValue\Slice\SliceValue;
use GoPhp\GoValue\String\BaseString;

/**
 * Value on which a slicing operation can be performed, e.g. x[1:2]
 */
interface Sliceable
{
    /**
     * Get slice of the value from the given start index ($low) to the given end index ($high).
     */
    public function slice(?int $low, ?int $high, ?int $max = null): BaseString|SliceValue;
}
