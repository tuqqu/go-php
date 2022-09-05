<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\RefType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\Operator;

use function GoPhp\assert_values_compatible;

final class NilValue implements AddressableValue
{
    use AddressableTrait;

    public function __construct(
        public readonly RefType $type = new UntypedNilType(), //fixme remove after nil type cleanup
    ) {}

    public function unwrap(): RefType
    {
        return $this->type;
    }

    public function operate(Operator $op): never
    {
        throw OperationError::undefinedOperator($op, $this, true);
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
        if ($this->type instanceof UntypedNilType) {
            throw OperationError::cannotAssign($this);
        }

        throw new \Exception();
    }

    public function copy(): self
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
