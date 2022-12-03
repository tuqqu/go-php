<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\NamedType;

interface Castable
{
    public function cast(NamedType $to): self;
}
