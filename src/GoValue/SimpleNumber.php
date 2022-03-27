<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\UnknownOperationError;
use GoPhp\Operator;
use function GoPhp\assert_type_conforms;

abstract class SimpleNumber implements GoValue
{
    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        $lhs = $this;

        assert_type_conforms($lhs, $rhs);

        return match ($op) {
            Operator::Plus => $lhs->add($rhs),
            Operator::Minus => $lhs->sub($rhs),
            Operator::Mul => $lhs->mul($rhs),
            Operator::Div => $lhs->div($rhs),
            Operator::Mod => $lhs->mod($rhs),
            Operator::EqEq => $lhs->equals($rhs),
            Operator::NotEq => $lhs->equals($rhs)->invert(),
            Operator::Greater => $lhs->greater($rhs),
            Operator::GreaterEq => $lhs->greaterEq($rhs),
            Operator::Less => $lhs->less($rhs),
            Operator::LessEq => $lhs->lessEq($rhs),
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
