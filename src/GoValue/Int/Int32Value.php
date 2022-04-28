<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int32Value extends BaseIntValue
{
    public const MIN = -2_147_483_648;
    public const MAX = +2_147_483_647;

    public function type(): NamedType
    {
        return NamedType::Int32;
    }
}
