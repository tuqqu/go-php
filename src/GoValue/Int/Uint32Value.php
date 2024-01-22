<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Uint32Value extends IntNumber
{
    public const int MIN = 0;
    public const int MAX = 4_294_967_295;

    public function type(): NamedType
    {
        return NamedType::Uint32;
    }
}
