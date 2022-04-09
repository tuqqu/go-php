<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoType\NamedType;

final class Float64Value extends BaseFloatValue
{
    public function type(): NamedType
    {
        return NamedType::Float64;
    }
}
