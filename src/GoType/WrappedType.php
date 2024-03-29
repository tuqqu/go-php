<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InterfaceTypeError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Unwindable;
use GoPhp\GoValue\WrappedValue;

use function GoPhp\construct_qualified_name;

/**
 * User-defined type that wraps another type
 *
 * @template-implements Unwindable<GoType>
 * @template-implements Hashable<string>
 */
final class WrappedType implements Unwindable, Hashable, GoType
{
    public function __construct(
        public readonly string $name,
        public readonly string $package,
        public readonly GoType $underlyingType,
    ) {}

    public function name(): string
    {
        return construct_qualified_name($this->name, $this->package);
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
        return $other instanceof self
            && $other->name === $this->name
            && $other->package === $this->package
            && $this->underlyingType->equals($other->underlyingType);
    }

    public function isCompatible(GoType $other): bool
    {
        if ($other instanceof UntypedType) {
            return $this->underlyingType->isCompatible($other);
        }

        return $this->equals($other);
    }

    public function zeroValue(): WrappedValue
    {
        return new WrappedValue(
            $this->underlyingType->zeroValue(),
            $this,
        );
    }

    /**
     * @return callable(GoValue): WrappedValue
     */
    public function valueCallback(): callable
    {
        return fn(GoValue $value): WrappedValue => new WrappedValue($value, $this);
    }

    public function convert(AddressableValue $value): WrappedValue
    {
        try {
            $convertedValue = $this->underlyingType->convert($value);
        } catch (InterfaceTypeError $e) {
            throw InterfaceTypeError::fromOther($e, $this);
        }

        return new WrappedValue($convertedValue, $this);
    }

    public function isLocal(string $package): bool
    {
        return $this->package === $package;
    }

    public function hash(): string
    {
        return $this->name . $this->underlyingType->name();
    }
}
