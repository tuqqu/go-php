<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Complex;

use GoPhp\GoType\NamedType;

final class Complex128Value extends ComplexNumber
{
    public function type(): NamedType
    {
        return NamedType::Complex128;
    }
}
