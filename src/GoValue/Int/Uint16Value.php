<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Uint16Value extends IntNumber
{
    public const int MIN = 0;
    public const int MAX = 65_535;

    public function type(): NamedType
    {
        return NamedType::Uint16;
    }
}
