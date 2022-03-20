<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

abstract class BaseIntValue extends BaseNumber implements Bitwise
{
    public function __construct(
        protected int $value,
    ) {}

    public static function fromString(string $digits): static
    {
        return new static((int) $digits);
    }

    // fixme move to int32
    public static function fromRune(string $rune): static
    {
        return new static(\mb_ord(\trim($rune, '\''), 'UTF-8'));
    }

    public function unwrap(): int
    {
        return $this->value;
    }

    // unary

    // bit

    public function bitwiseComplement(): static
    {
        return new static(~$this->value);
    }

    // arith

    public function negate(): static
    {
        return new static(-$this->value);
    }

    public function noop(): static
    {
        return $this;
    }

    // binary

    // fixme move all these to basenumber

    public function add(Addable $value): static
    {
        return new static($this->value + $value->value);
    }

    public function sub(Number $value): static
    {
        return new static($this->value - $value->value);
    }

    public function greater(Comparable $other): BoolValue
    {
        return BoolValue::fromBool($this->value > $other->value);
    }

    public function greaterEq(Comparable $other): BoolValue
    {
        return BoolValue::fromBool($this->value >= $other->value);
    }

    public function less(Comparable $other): BoolValue
    {
        return BoolValue::fromBool($this->value < $other->value);
    }

    public function lessEq(Comparable $other): BoolValue
    {
        return BoolValue::fromBool($this->value <= $other->value);
    }

    public function div(Number $value): Number
    {
        return new static($this->value / $value->value);
    }

    public function mod(Number $value): Number
    {
        return new static($this->value % $value->value);
    }

    public function mul(Number $value): Number
    {
        return new static($this->value * $value->value);
    }
}
