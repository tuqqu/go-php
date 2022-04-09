<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class Uint8Value extends BaseIntValue
{
    public const MIN = 0;
    public const MAX = 255;

    public function type(): NamedType
    {
        return NamedType::Uint8;
    }
}
