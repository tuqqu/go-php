<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class IntValue extends BaseIntValue
{
    public function type(): BasicType
    {
        return BasicType::Int;
    }
}
