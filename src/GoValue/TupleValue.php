<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\ValueType;
use GoPhp\Operator;

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
        throw new \BadMethodCallException('cannot operate');
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

    /**
     * @return GoValue[]
     */
    public function unwrap(): array
    {
        return $this->values;
    }

    public function type(): ValueType
    {
        throw new \BadMethodCallException(); //fixme
    }
}
