<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\RefType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoType\WrappedType;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;

final class NilValue implements GoValue
{
    use NamedTrait;

    public function __construct(
        public readonly RefType $type,
    ) {}

    public function unwrap(): RefType
    {
        return $this->type;
    }

    public function operate(Operator $op): never
    {
        throw new \Exception();
    }

    public function operateOn(Operator $op, GoValue $rhs): BoolValue
    {
        if ($rhs->type() instanceof UntypedNilType) {
            throw new \Exception('operator == not defined on untyped nil');
        }

        assert_values_compatible($this, $rhs);

        return match ($op) {
            Operator::EqEq => $this->equals($rhs),
            Operator::NotEq => $this->equals($rhs)->invert(),
            default => throw OperationError::undefinedOperator($op, $this),
        };
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return new BoolValue($rhs instanceof self);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \Exception();
    }

    public function copy(): static
    {
        return $this;
    }

    public function type(): RefType
    {
       return $this->type;
    }

    public function toString(): string
    {
        return '<nil>';
    }
}
