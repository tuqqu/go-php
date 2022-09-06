<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\UntypedNilType;
use GoPhp\Operator;

final class UntypedNilValue implements AddressableValue
{
    use AddressableTrait;

    public readonly UntypedNilType $type;

    public function __construct()
    {
        $this->type = new UntypedNilType();
    }

    public function unwrap(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operate(Operator $op): never
    {
        throw OperationError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        if ($rhs instanceof self) {
            throw OperationError::undefinedOperator($op, $this);
        }

        return $rhs->operateOn($op, $this);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return new BoolValue($rhs instanceof self);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        if ($op === Operator::Eq) {
            throw OperationError::cannotAssign($this);
        }

        throw TypeError::mismatchedTypes($this->type, $rhs->type());
    }

    public function copy(): self
    {
        return $this;
    }

    public function type(): UntypedNilType
    {
       return $this->type;
    }

    public function toString(): string
    {
        return '<nil>';
    }
}
