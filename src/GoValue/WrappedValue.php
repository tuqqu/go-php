<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;
use GoPhp\GoType\WrappedType;
use GoPhp\Operator;

final class WrappedValue implements GoValue
{
    public function __construct(
        public readonly GoValue $underlyingValue,
        public readonly WrappedType $wrappedType,
    ) {}

    public function unwrap(): GoType
    {
        return $this->underlyingValue->unwrap();
    }

    public function operate(Operator $op): GoValue
    {
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

    public function equals(GoValue $rhs): BoolValue
    {
        return $this->underlyingValue->equals($rhs);
    }

    public function copy(): self
    {
        return $this;
    }

    public function type(): GoType
    {
        return $this->wrappedType;
    }

    public function toString(): string
    {
        return $this->underlyingValue->toString();
    }

    public function isNamed(): bool
    {
        return true;
    }

    public function makeNamed(): void {}

    private function enwrapNew(GoValue $value): self
    {
        return new self($value, $this->wrappedType);
    }
}
