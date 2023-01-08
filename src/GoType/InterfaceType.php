<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Interface\InterfaceValue;

// fixme: this is a stub for now
final class InterfaceType implements GoType
{
    public function name(): string
    {
        return '{}interface';
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self;
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof UntypedNilType || $this->equals($other);
    }

    public function reify(): self
    {
        return $this;
    }

    public function zeroValue(): InterfaceValue
    {
        return InterfaceValue::nil($this);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
