<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoType\BasicType;

final class UntypedFloatValue extends BaseFloatValue
{
    public static function fromString(string $digits): self
    {
        return new self((float) $digits);
    }

    public function type(): BasicType
    {
        return BasicType::UntypedFloat;
    }
}
