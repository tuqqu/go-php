<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\UnknownOperationError;
use GoPhp\Operator;
use function GoPhp\assert_type_conforms;

abstract class SimpleNumber implements GoValue
{
    public function toString(): string
    {
        return (string) $this->value;
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        assert_type_conforms($this, $rhs);

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
            default => throw UnknownOperationError::unknownOperator($op),
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
                throw UnknownOperationError::unknownOperator($op);
        }
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        assert_type_conforms($this, $rhs);

        match ($op) {
            Operator::PlusEq,
            Operator::Inc => $this->mutAdd($rhs),
            Operator::MinusEq,
            Operator::Dec => $this->mutSub($rhs),
            Operator::MulEq => $this->mutMul($rhs),
            Operator::DivEq => $this->mutDiv($rhs),
            Operator::ModEq => $this->mutMod($rhs),
            default => throw UnknownOperationError::unknownOperator($op),
        };
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
