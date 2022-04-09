<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int8Value extends BaseIntValue
{
    public const MIN = -128;
    public const MAX = +127;

    public function type(): NamedType
    {
        return NamedType::Int8;
    }
}
