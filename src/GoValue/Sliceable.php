<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value on which a slicing operation can be performed, e.g. x[1:2]
 */
interface Sliceable
{
    public function slice(?int $low, ?int $high, ?int $max = null): self;
}
