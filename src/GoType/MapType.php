<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;

final class MapType implements RefType
{
    public readonly string $name;

    public function __construct(
        public readonly GoType $keyType,
        public readonly GoType $elemType,
    ) {
        $this->name = \sprintf('map[%s]%s', $this->keyType->name(), $this->elemType->name());
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self
            && $this->keyType->equals($other->keyType)
            && $this->elemType->equals($other->elemType);
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof UntypedNilType || $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        return new NilValue($this);
    }
}
