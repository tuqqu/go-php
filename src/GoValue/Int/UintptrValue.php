<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\NamedType;

final class UintptrValue extends BaseIntValue
{
//    public const MIN = 0;
//    public const MAX = 18446744073709551615; //fixme

    public function type(): NamedType
    {
        return NamedType::Uintptr;
    }
}
