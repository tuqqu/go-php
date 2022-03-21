<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

final class IntValue extends BaseNumber implements Bitwise
{
    public function __construct(
        int $value,
        ValueType $valueType,
    ) {
        $this->value = $value;
        $this->valueType = $valueType;
    }
    public static function fromString(string $digits, ValueType $valueType): static
    {
        return new static((int) $digits, $valueType);
    }

    public static function fromRune(string $rune): static
    {
        return new static(\mb_ord(\trim($rune, '\''), 'UTF-8'), ValueType::Int32);
    }

    public function unwrap(): int
    {
        return $this->value;
    }

    public function bitwiseComplement(): static
    {
        return new static(~$this->value, $this->valueType);
    }

    public function type(): ValueType
    {
        return $this->valueType;
    }
}
