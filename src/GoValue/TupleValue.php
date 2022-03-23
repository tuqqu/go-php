<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\ValueType;
use GoPhp\Operator;

final class TupleValue implements GoValue
{
    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public readonly array $values,
    ) {}

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

    /**
     * @return GoValue[]
     */
    public function unwrap(): array
    {
        return $this->values;
    }

    public function type(): ValueType
    {
        return $this->values[0]->type();
        //fixme add
    }
}
