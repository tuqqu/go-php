<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;

final class PointerType implements RefType
{
    public function __construct(
        public readonly GoType $pointsTo,
    ) {}

    public function name(): string
    {
        return \sprintf('*%s', $this->pointsTo->name());
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self
            && $this->pointsTo->equals($other->pointsTo);
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): AddressValue
    {
        return AddressValue::fromType($this);
    }

    public function convert(GoValue $value): GoValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
