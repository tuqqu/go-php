<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class UintptrValue extends IntNumber
{
    public const int MIN = 0;
    public const float MAX = 18_446_744_073_709_551_615;

    public function type(): NamedType
    {
        return NamedType::Uintptr;
    }
}
