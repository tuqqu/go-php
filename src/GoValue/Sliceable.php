<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Sliceable
{
    public function slice(?int $low, ?int $high, ?int $max = null): self;
}
