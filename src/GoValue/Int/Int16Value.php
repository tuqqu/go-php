<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Int16Value extends BaseIntValue
{
    public const MIN = -32_768;
    public const MAX = +32_767;

    public function type(): BasicType
    {
        return BasicType::Int16;
    }
}
