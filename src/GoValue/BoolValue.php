<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoParser\Ast\Operator;
use GoParser\Lexer\Token;
use GoPhp\Error\UnknownOperationError;

enum BoolValue: int implements GoValue
{
    case False = 0;
    case True = 1;

    public static function fromBool(bool $value): self
    {
        return self::from((int) $value);
    }

    public function type(): ValueType
    {
        return ValueType::Bool;
    }

    public function unwrap(): bool
    {
        return (bool) $this->value;
    }

    public function invert(): self
    {
        return self::fromBool($this->unwrap());
    }

    public function operate(Operator $op): GoValue
    {
        return match ($op->value) {
            Token::LogicNot->value => $this->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function operateOn(Operator $op, GoValue $rhs): self|BoolValue
    {
        // fixme add type check

        return match ($op->value) {
            Token::EqEq->value => $this->equals($rhs),
            Token::NotEq->value => $this->equals($rhs)->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function equals(GoValue $rhs): self
    {
        return self::fromBool($this === $rhs);
    }
}
