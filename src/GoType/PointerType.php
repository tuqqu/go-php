<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\PointerValue;

/**
 * @see https://golang.org/ref/spec#Pointer_types
 *
 * @template T of GoType
 */
final class PointerType implements RefType
{
    /**
     * @param T $pointsTo
     */
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

    public function zeroValue(): PointerValue
    {
        return PointerValue::nil($this);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
