<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;

interface Castable
{
    public function cast(GoType $to): self;
}
