<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\UntypedNilType;
use GoPhp\Operator;

/**
 * @template-implements AddressableValue<never>
 */
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
        throw RuntimeError::undefinedOperator($op, $this, true);
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        if ($rhs instanceof self) {
            throw RuntimeError::undefinedOperator($op, $this);
        }

        return $rhs->operateOn($op, $this);
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        if ($op === Operator::Eq) {
            throw RuntimeError::cannotAssign($this);
        }

        throw RuntimeError::mismatchedTypes($this->type, $rhs->type());
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
