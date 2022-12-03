<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\PointerValue;

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
        return $other instanceof UntypedNilType
            || $other instanceof self
            && $this->pointsTo->equals($other->pointsTo);
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): self
    {
        return $this;
    }

    public function defaultValue(): PointerValue
    {
        return PointerValue::nil($this);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
