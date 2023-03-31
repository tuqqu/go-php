<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\GoType\WrappedType;
use GoPhp\Operator;

/**
 * Value with a wrapped type, i.e. a defined type.
 *
 * ```
 * type myInt int
 * var x myInt = 42
 *     ^ x is a wrapped value
 *```
 *
 * @template-implements AddressableValue<never>
 * @template-implements Unwindable<GoValue>
 */
final class WrappedValue implements Unwindable, AddressableValue
{
    use AddressableTrait;

    public function __construct(
        public readonly GoValue $underlyingValue,
        public readonly WrappedType $wrappedType,
    ) {}

    public function unwind(): GoValue
    {
        $value = $this;

        while ($value instanceof self) {
            $value = $value->underlyingValue;
        }

        return $value;
    }

    public function operate(Operator $op): GoValue
    {
        if ($op === Operator::BitAnd) {
            return PointerValue::fromValue($this);
        }

        return $this->enwrapNew($this->underlyingValue->operate($op));
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        return $this->enwrapNew($this->underlyingValue->operateOn($op, $rhs));
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->underlyingValue->mutate($op, $rhs);
    }

    public function copy(): self
    {
        return new self(
            $this->underlyingValue->copy(),
            $this->wrappedType,
        );
    }

    public function type(): WrappedType
    {
        return $this->wrappedType;
    }

    public function toString(): string
    {
        return $this->underlyingValue->toString();
    }

    public function unwrap(): never
    {
        throw InternalError::unreachable($this);
    }

    private function enwrapNew(GoValue $value): self
    {
        return new self($value, $this->wrappedType);
    }
}
