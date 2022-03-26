<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Uint64Value extends BaseIntValue
{
//    public const MIN = 0;
//    public const MAX = 18446744073709551615; //fixme float

    public function type(): BasicType
    {
        return BasicType::Uint64;
    }
}
