<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int8Value extends IntNumber
{
    public const int MIN = -128;
    public const int MAX = +127;

    public function type(): NamedType
    {
        return NamedType::Int8;
    }
}
