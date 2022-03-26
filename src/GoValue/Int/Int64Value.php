<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Int64Value extends BaseIntValue
{
    public function type(): BasicType
    {
        return BasicType::Int64;
    }
}
