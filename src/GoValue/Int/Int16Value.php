<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int16Value extends IntNumber
{
    public const int MIN = -32_768;
    public const int MAX = +32_767;

    public function type(): NamedType
    {
        return NamedType::Int16;
    }
}
