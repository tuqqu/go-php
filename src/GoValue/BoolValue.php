<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Operator;
use GoPhp\GoType\BasicType;
use GoPhp\Error\UnknownOperationError;
use function GoPhp\assert_type_conforms;

enum BoolValue: int implements GoValue
{
    case False = 0;
    case True = 1;

    public function toString(): string
    {
        return $this->value === 0 ? 'false' : 'true';
    }

    public static function fromBool(bool $value): self
    {
        return self::from((int) $value);
    }

    public function type(): BasicType
    {
        return BasicType::Bool;
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
        assert_type_conforms($this, $rhs);

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw UnknownOperationError::unknownOperator($op),
        };
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw UnknownOperationError::unknownOperator($op);
    }

    public function equals(GoValue $rhs): self
    {
        return self::fromBool($this === $rhs);
    }

    public function copy(): static
    {
        return $this;
    }
}
