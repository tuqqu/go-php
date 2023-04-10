<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Float;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sealable;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\Operator;

use function sprintf;

/**
 * @template-extends SimpleNumber<float>
 */
abstract class FloatNumber extends SimpleNumber
{
    public const NAME = 'float';

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
        return sprintf('%f', $this->value);
    }

    abstract public function type(): BasicType;

    final protected function negate(): static
    {
        return new static(-$this->value);
    }

    // binary

    final protected function add(parent $value): static
    {
        return new static($this->value + $value->unwrap());
    }

    final protected function sub(parent $value): static
    {
        return new static($this->value - $value->unwrap());
    }

    final protected function div(parent $value): static
    {
        return new static($this->value / $value->unwrap());
    }

    final protected function mod(parent $value): static
    {
        return new static($this->value % $value->unwrap());
    }

    final protected function mul(parent $value): static
    {
        return new static($this->value * $value->unwrap());
    }

    final protected function mutAdd(parent $value): void
    {
        $this->value += $value->unwrap();
    }

    final protected function mutSub(parent $value): void
    {
        $this->value += $value->unwrap();
    }

    final protected function mutDiv(parent $value): void
    {
        $this->value /= $value->unwrap();
    }

    final protected function mutMod(parent $value): void
    {
        $this->value %= $value->unwrap();
    }

    final protected function mutMul(parent $value): void
    {
        $this->value *= $value->unwrap();
    }

    final protected function assign(parent $value): void
    {
        $this->value = $value->unwrap();
    }

    final protected function doBecomeTyped(NamedType $type): AddressableValue&Sealable
    {
        if ($type->isInt() && $this->type() === UntypedType::UntypedRoundFloat) {
            return $this->convertTo($type);
        }

        $number = $this->unwrap();

        return match ($type) {
            NamedType::Float32 => new Float32Value($number),
            NamedType::Float64 => new Float64Value($number),
            default => throw RuntimeError::implicitConversionError($this, $type),
        };
    }

    final protected function completeOperate(Operator $op): never
    {
        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    final protected function completeOperateOn(Operator $op, GoValue $rhs): never
    {
        throw RuntimeError::undefinedOperator($op, $this);
    }

    final protected function completeMutate(Operator $op, GoValue $rhs): void
    {
        throw RuntimeError::undefinedOperator($op, $this);
    }
}
