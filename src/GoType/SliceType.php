<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;
use GoPhp\GoValue\Slice\SliceValue;

final class SliceType implements RefType
{
    public readonly string $name;

    public function __construct(
        public readonly GoType $elemType,
    ) {
        $this->name = \sprintf('[]%s', $this->elemType->name());
    }

    public static function fromArrayType(ArrayType $arrayType): self
    {
        return new self($arrayType->elemType);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self && $this->elemType->equals($other->elemType);
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
        return SliceValue::nil($this);
    }
}
