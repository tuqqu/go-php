<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class UintValue extends IntNumber
{
    public const MIN = 0;
    public const MAX = 1.844_674_407_4e19;

    public function type(): NamedType
    {
        return NamedType::Uint;
    }
}
