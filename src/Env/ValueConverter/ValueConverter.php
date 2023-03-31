<?php

declare(strict_types=1);

namespace GoPhp\Env\ValueConverter;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

interface ValueConverter
{
    public function supports(GoValue $value, GoType $type): bool;

    public function convert(GoValue $value, GoType $type): GoValue;
}
