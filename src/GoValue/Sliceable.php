<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoValue\Slice\SliceValue;

interface Sliceable
{
    public function slice(?int $low, ?int $high, ?int $max = null): StringValue|SliceValue;
}
