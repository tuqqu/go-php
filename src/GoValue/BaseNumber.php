<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoParser\Ast\Operator;
use GoParser\Lexer\Token;
use GoPhp\Error\UnknownOperationError;

abstract class BaseNumber implements Number, Comparable
{
    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        //fixme check rhs

        $lhs = $this;
        switch ($op->value) {
            // math
            case Token::Plus->value:
            case Token::PlusEq->value:
                return $lhs->add($rhs);
                break;

            case Token::Minus->value:
            case Token::MinusEq->value:
                return $lhs->sub($rhs);

            case Token::Mul->value:
            case Token::MulEq->value:
                return $lhs->mul($rhs);

            case Token::Div->value:
            case Token::DivEq->value:
                return $lhs->div($rhs);

            case Token::Mod->value:
            case Token::ModEq->value:
                return $lhs->mod($rhs);

            // eq
            case Token::EqEq->value:
                return $lhs->equals($rhs);

            case Token::NotEq->value:
                return $lhs->equals($rhs)->invert();

            // comparison
            case Token::Greater->value:
                return $lhs->greater($rhs);

            case Token::GreaterEq->value:
                return $lhs->greaterEq($rhs);

            case Token::Less->value:
                return $lhs->less($rhs);

            case Token::LessEq->value:
                return $lhs->lessEq($rhs);

            default:
                throw UnknownOperationError::unknownOperator($op);
        }
    }

    public function operate(Operator $op): self
    {
        // fixme check rhs

        switch ($op->value) {
            case Token::Plus->value:
                return $this->noop();
            case Token::Minus->value:
                return $this->negate();
            case Token::LogicNot->value:
                return $this->invert();
            case Token::BitXor->value:
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
}
