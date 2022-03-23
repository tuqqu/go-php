<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\BasicType;
use GoPhp\Error\UnknownOperationError;

abstract class BaseNumber implements Number, Comparable
{
    protected int|float $value;

    protected BasicType $valueType;

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        //fixme check rhs

        $lhs = $this;
        switch ($op) {
            // math
            case Operator::Plus:
                return $lhs->add($rhs);
                break;

            case Operator::Minus:
                return $lhs->sub($rhs);

            case Operator::Mul:
                return $lhs->mul($rhs);

            case Operator::Div:
                return $lhs->div($rhs);

            case Operator::Mod:
                return $lhs->mod($rhs);

            // eq
            case Operator::EqEq:
                return $lhs->equals($rhs);

            case Operator::NotEq:
                return $lhs->equals($rhs)->invert();

            // comparison
            case Operator::Greater:
                return $lhs->greater($rhs);

            case Operator::GreaterEq:
                return $lhs->greaterEq($rhs);

            case Operator::Less:
                return $lhs->less($rhs);

            case Operator::LessEq:
                return $lhs->lessEq($rhs);

            default:
                throw UnknownOperationError::unknownOperator($op);
        }
    }

    public function operate(Operator $op): self
    {
        // fixme check rhs

        switch ($op) {
            case Operator::Plus:
                return $this->noop();
            case Operator::Minus:
                return $this->negate();
            case Operator::LogicNot:
                return $this->invert();
            case Operator::BitXor:
                // fixme move to ints
                return $this->bitwiseComplement();
            default:
                throw UnknownOperationError::unknownOperator($op);
        }
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return BoolValue::fromBool($this->unwrap() === $rhs->unwrap());
    }


    // unary

    // bit

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
        return new static($this->value + $value->value, $this->valueType);
    }

    public function sub(Number $value): static
    {
        return new static($this->value - $value->value, $this->valueType);
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

    public function div(Number $value): static
    {
        return new static($this->value / $value->value, $this->valueType);
    }

    public function mod(Number $value): static
    {
        return new static($this->value % $value->value, $this->valueType);
    }

    public function mul(Number $value): static
    {
        return new static($this->value * $value->value, $this->valueType);
    }
}
