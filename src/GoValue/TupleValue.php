<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\GoType\GoType;
use GoPhp\Operator;

/**
 * Not a real Go type, but an internal representation
 * of a set of values returned from a function call.
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
        throw OperationError::unsupportedOperation(__METHOD__, $this);
    }

    public function operate(Operator $op): self
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function operateOn(Operator $op, GoValue $rhs): self
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function equals(GoValue $rhs): BoolValue
    {
        throw new \BadMethodCallException(); //fixme
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw new \BadMethodCallException('cannot operate');
    }

    public function copy(): static
    {
        throw new \BadMethodCallException('cannot operate');
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
        throw new \BadMethodCallException(); //fixme
    }
}
