<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoValue\Float\Float32Value;
use GoPhp\GoValue\Float\Float64Value;
use GoPhp\GoValue\Int\Int16Value;
use GoPhp\GoValue\Int\Int32Value;
use GoPhp\GoValue\Int\Int64Value;
use GoPhp\GoValue\Int\Int8Value;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Uint16Value;
use GoPhp\GoValue\Int\Uint32Value;
use GoPhp\GoValue\Int\Uint64Value;
use GoPhp\GoValue\Int\Uint8Value;
use GoPhp\GoValue\Int\UintptrValue;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\Operator;
use function GoPhp\assert_values_compatible;

abstract class SimpleNumber implements GoValue
{
    final public function convertTo(BasicType $type): self
    {
        $number = $this->unwrap();

        return match ($type) {
            BasicType::Int => new IntValue($number),
            BasicType::Int8 => new Int8Value($number),
            BasicType::Int16 => new Int16Value($number),
            BasicType::Int32 => new Int32Value($number),
            BasicType::Int64 => new Int64Value($number),
            BasicType::Uint => new UintValue($number),
            BasicType::Uint8 => new Uint8Value($number),
            BasicType::Uint16 => new Uint16Value($number),
            BasicType::Uint32 => new Uint32Value($number),
            BasicType::Uint64 => new Uint64Value($number),
            BasicType::Uintptr => new UintptrValue($number),
            BasicType::Float32 => new Float32Value($number),
            BasicType::Float64 => new Float64Value($number),
            default => throw TypeError::conversionError($this->type(), $type),
        };
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::Plus => $this->add($rhs),
            Operator::Minus => $this->sub($rhs),
            Operator::Mul => $this->mul($rhs),
            Operator::Div => $this->div($rhs),
            Operator::Mod => $this->mod($rhs),
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            Operator::Greater => $this->greater($rhs),
            Operator::GreaterEq => $this->greaterEq($rhs),
            Operator::Less => $this->less($rhs),
            Operator::LessEq => $this->lessEq($rhs),
            default => throw OperationError::unknownOperator($op, $this),
        };
    }

    public function operate(Operator $op): self
    {
        switch ($op) {
            case Operator::Plus:
                return $this->noop();
            case Operator::Minus:
                return $this->negate();
            case Operator::BitXor:
                // fixme move to ints
                return $this->bitwiseComplement();
            default:
                throw OperationError::unknownOperator($op, $this);
        }
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        assert_values_compatible($this, $rhs);

        match ($op) {
            Operator::PlusEq,
            Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq,
            Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            Operator::ModEq => $this->mutMod($rhs),
            default => throw OperationError::unknownOperator($op, $this),
        };
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::fromBool($this->value === $rhs->unwrap());
    }

    public function greater(self $other): BoolValue
    {
        return BoolValue::fromBool($this->value > $other->value);
    }

    public function greaterEq(self $other): BoolValue
    {
        return BoolValue::fromBool($this->value >= $other->value);
    }

    public function less(self $other): BoolValue
    {
        return BoolValue::fromBool($this->value < $other->value);
    }

    public function lessEq(self $other): BoolValue
    {
        return BoolValue::fromBool($this->value <= $other->value);
    }
}
