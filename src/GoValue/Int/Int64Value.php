<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Int64Value extends BaseIntValue
{
    public function type(): NamedType
    {
        return NamedType::Int64;
    }
}
