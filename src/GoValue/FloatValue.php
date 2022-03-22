<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\ValueType;

final class FloatValue extends BaseNumber
{
    public function __construct(
        float $value,
        ValueType $valueType,
    ) {
        $this->value = $value;
        $this->valueType = $valueType;
    }

    public static function fromString(string $digits, ValueType $valueType): static
    {
        return new static((float) $digits, $valueType);
    }

    public function unwrap(): float
    {
        return $this->value;
    }

    public function type(): ValueType
    {
        return $this->valueType;
    }
}
