<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\BasicType;

final class FloatValue extends BaseNumber
{
    public function __construct(
        float     $value,
        BasicType $valueType,
    ) {
        $this->value = $value;
        $this->valueType = $valueType;
    }

    public static function fromString(string $digits, BasicType $valueType): static
    {
        return new static((float) $digits, $valueType);
    }

    public function unwrap(): float
    {
        return $this->value;
    }

    public function type(): BasicType
    {
        return $this->valueType;
    }
}
