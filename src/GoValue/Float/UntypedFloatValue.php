<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\GoType\UntypedType;

final class UntypedFloatValue extends FloatNumber
{
    private readonly UntypedType $type;

    public function __construct(float $value)
    {
        parent::__construct($value);

        $this->type = ($value - (int) $value) === 0.0
            ? UntypedType::UntypedRoundFloat
            : UntypedType::UntypedFloat;
    }

    public static function fromString(string $digits): self
    {
        return new self((float) $digits);
    }

    public function type(): UntypedType
    {
        return $this->type;
    }
}
