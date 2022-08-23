<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\Error\TypeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\SimpleNumber;

/**
 * @template-extends SimpleNumber<self>
 */
abstract class BaseFloatValue extends SimpleNumber
{
    protected float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function unwrap(): float
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    abstract public function type(): BasicType;

    final protected function negate(): static
    {
        return new static(-$this->value);
    }

    // binary

    final protected function add(parent $value): static
    {
        return new static($this->value + $value->value);
    }

    final protected function sub(parent $value): static
    {
        return new static($this->value - $value->value);
    }

    final protected function div(parent $value): static
    {
        return new static($this->value / $value->value);
    }

    final protected function mod(parent $value): static
    {
        return new static($this->value % $value->value);
    }

    final protected function mul(parent $value): static
    {
        return new static($this->value * $value->value);
    }

    final protected function mutAdd(parent $value): void
    {
        $this->value += $value->value;
    }

    final protected function mutSub(parent $value): void
    {
        $this->value += $value->value;
    }

    final protected function mutDiv(parent $value): void
    {
        $this->value /= $value->value;
    }

    final protected function mutMod(parent $value): void
    {
        $this->value %= $value->value;
    }

    final protected function mutMul(parent $value): void
    {
        $this->value *= $value->value;
    }

    final protected function assign(parent $value): void
    {
        $this->value = $value->value;
    }

    final protected function doBecomeTyped(NamedType $type): parent
    {
        $number = $this->unwrap();

        if ($type->isInt() && $this->type() === UntypedType::UntypedRoundFloat) {
            return $this->convertTo($type);
        }

        return match ($type) {
            NamedType::Float32 => new Float32Value($number),
            NamedType::Float64 => new Float64Value($number),
            default => throw TypeError::implicitConversionError($this, $type),
        };
    }
}
