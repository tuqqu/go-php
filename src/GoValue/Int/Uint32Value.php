<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Uint32Value extends BaseIntValue
{
    public const MIN = 0;
    public const MAX = 4_294_967_295;

    public function type(): BasicType
    {
        return BasicType::Uint32;
    }
}
