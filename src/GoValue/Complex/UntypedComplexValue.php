<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Complex;

use GoPhp\GoType\UntypedType;

final class UntypedComplexValue extends ComplexNumber
{
    public static function fromString(string $digits): self
    {
        return new self(0.0, (float) \substr($digits, 0, -1));
    }

    public function type(): UntypedType
    {
        return UntypedType::UntypedComplex;
    }
}
