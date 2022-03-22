<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\ValueType;
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
        return self::fromBool(!$this->unwrap());
    }

    public function operate(Operator $op): self
    {
        return match ($op) {
            Operator::LogicNot => $this->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function operateOn(Operator $op, GoValue $rhs): self
    {
        // fixme add type check

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function equals(GoValue $rhs): self
    {
        return self::fromBool($this === $rhs);
    }
}
