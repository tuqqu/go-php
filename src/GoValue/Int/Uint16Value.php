<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\BasicType;

final class Uint16Value extends BaseIntValue
{
    public const MIN = 0;
    public const MAX = 65_535;

    public function type(): BasicType
    {
        return BasicType::Uint16;
    }
}
