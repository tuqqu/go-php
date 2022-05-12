<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\Error\TypeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\SimpleNumber;

abstract class BaseFloatValue extends SimpleNumber
{
    public float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
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

    // binary

    public function add(self $value): static
    {
        return new static($this->value + $value->value);
    }

    public function sub(self $value): static
    {
        return new static($this->value - $value->value);
    }

    public function div(self $value): static
    {
        return new static($this->value / $value->value);
    }

    public function mod(self $value): static
    {
        return new static($this->value % $value->value);
    }

    public function mul(self $value): static
    {
        return new static($this->value * $value->value);
    }

    public function mutAdd(self $value): void
    {
        $this->value += $value->value;
    }

    public function mutSub(self $value): void
    {
        $this->value += $value->value;
    }

    public function mutDiv(self $value): void
    {
        $this->value /= $value->value;
    }

    public function mutMod(self $value): void
    {
        $this->value %= $value->value;
    }

    public function mutMul(self $value): void
    {
        $this->value *= $value->value;
    }

    final protected function doBecomeTyped(NamedType $type): self
    {
        if ($this->type() !== UntypedType::UntypedFloat) {
            throw TypeError::implicitConversionError($this, $type);
        }

        $number = $this->unwrap();

        return match ($type) {
            NamedType::Float32 => new Float32Value($number),
            NamedType::Float64 => new Float64Value($number),
            default => throw TypeError::implicitConversionError($this, $type),
        };
    }
}
