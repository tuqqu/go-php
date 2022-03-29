<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

use GoPhp\GoType\ArrayType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;

final class ArrayValue implements GoValue
{
    private readonly int $len;

    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public array $values,
        public readonly ArrayType $type,
    ) {
        $this->len = \count($this->values);

        if ($this->type->isUnfinished()) {
            $this->type->setLen($this->len);
        } elseif ($this->type->len !== $this->len) {
            throw new \Exception('wrong type');
        }
    }

    public function toString(): string
    {
        $str = [];
        foreach ($this->values as $value) {
            $str[] = $value->toString();
        }

        return \sprintf('[%s]', \implode(' ', $str));
    }

    public function get(?int $at = null): GoValue
    {
        if ($at !== null && $at >= $this->len) {
            throw new \OutOfBoundsException();
        }

        return $this->values[$at];
    }

    public function set(GoValue $value, int $at): void
    {
        if ($at >= $this->len) {
            throw new \OutOfBoundsException();
        }

        if (!$value->type()->conforms($this->type->internalType)) {
            throw new \Exception('typeerr');
        }

        $this->values[$at] = $value;
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

    public function type(): ArrayType
    {
        return $this->type;
    }

    public function copy(): static
    {
        return clone $this; // fixme iterate and clone all?
    }
}
