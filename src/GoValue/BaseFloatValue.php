<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

abstract class BaseFloatValue extends BaseNumber
{
    public function __construct(
        protected float $value,
    ) {}

    public static function fromString(string $digits): static
    {
        return new static((float) $digits);
    }

    public function unwrap(): float
    {
        return $this->value;
    }

    public function negate(): static
    {
        return new static(-$this->value);
    }

    public function noop(): static
    {
        return $this;
    }

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
