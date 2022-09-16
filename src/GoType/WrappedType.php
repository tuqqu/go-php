<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\WrappedValue;

final class WrappedType implements GoType
{
    public function __construct(
        public readonly string $name,
        public readonly GoType $underlyingType,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function unwind(): GoType
    {
        $type = $this;

        while ($type instanceof self) {
            $type = $type->underlyingType;
        }

        return $type;
    }

    public function equals(GoType $other): bool
    {
        // fixme check `type a struct{} == struct{}`
        return $other instanceof self
            && $other->name === $this->name
            && $this->underlyingType->equals($other->underlyingType);
    }

    public function isCompatible(GoType $other): bool
    {
        if ($other instanceof UntypedType) {
            return $this->underlyingType->isCompatible($other);
        }

        return $this->equals($other);
    }

    public function reify(): GoType
    {
        return $this;
    }

    public function defaultValue(): WrappedValue
    {
        return new WrappedValue(
            $this->underlyingType->defaultValue(),
            $this,
        );
    }

    public function valueCallback(): callable
    {
        return fn (GoValue $value): WrappedValue => new WrappedValue($value, $this);
    }

    public function convert(GoValue $value): WrappedValue
    {
        return new WrappedValue(
            $this->underlyingType->convert($value),
            $this,
        );
    }
}
