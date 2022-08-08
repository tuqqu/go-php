<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * Not a real Go type, but an internal representation
 * of a set of values returned from a function call with multiple return values.
 */
final class TupleValue implements GoValue
{
    public readonly int $len;

    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public readonly array $values,
    ) {
        $this->len = \count($this->values);
    }

    public function toString(): string
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operate(Operator $op): self
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operateOn(Operator $op, GoValue $rhs): self
    {
        throw InternalError::unreachableMethodCall();
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw InternalError::unreachableMethodCall();
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function copy(): static
    {
        throw InternalError::unreachableMethodCall();
    }

    /**
     * @return GoValue[]
     */
    public function unwrap(): array
    {
        return $this->values;
    }

    public function type(): GoType
    {
        throw InternalError::unreachableMethodCall();
    }

    public function isNamed(): bool
    {
        throw InternalError::unreachableMethodCall();
    }

    public function makeNamed(): void
    {
        throw InternalError::unreachableMethodCall();
    }
}
