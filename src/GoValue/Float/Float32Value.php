<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoType\BasicType;

final class Float32Value extends BaseFloatValue
{
    public function type(): BasicType
    {
        return BasicType::Float32;
    }
}
