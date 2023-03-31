<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Env\Environment;
use GoPhp\Error\InterfaceTypeError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Interface\InterfaceValue;
use GoPhp\GoValue\WrappedValue;

use function implode;
use function sprintf;

/**
 * @template-implements Hashable<string>
 */
final class InterfaceType implements Hashable, GoType
{
    /**
     * @param array<string, FuncType> $methods
     */
    public function __construct(
        private readonly array $methods = [],
        private readonly ?Environment $envRef = null,
    ) {}

    public function name(): string
    {
        $methods = [];
        foreach ($this->methods as $name => $method) {
            $methods[] = sprintf('%s() %s', $name, $method->returns);
        }

        return sprintf(
            '%s{%s}',
            InterfaceValue::NAME,
            implode('; ', $methods),
        );
    }

    public function equals(GoType $other): bool
    {
        return match (true) {
            $other instanceof UntypedNilType,
            $other instanceof self => true,
            $other instanceof WrappedType => $this->checkWrappedType($other),
            default => false,
        };
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof UntypedNilType
            || $this->equals($other);
    }

    public function zeroValue(): InterfaceValue
    {
        return InterfaceValue::nil($this);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        if (!$value instanceof WrappedValue) {
            return $value;
        }

        if ($this->checkWrappedType($value->type())) {
            return $value;
        }

        throw InterfaceTypeError::cannotUseAsInterfaceType($value, $this);
    }

    public function hash(): string
    {
        return $this->name();
    }

    public function tryGetMissingMethod(WrappedType $other): ?string
    {
        if ($this->envRef === null) {
            return null;
        }

        foreach ($this->methods as $name => $method) {
            if (
                !$this->envRef->hasMethod($name, $other)
                && !$this->envRef->hasMethod($name, $other->underlyingType)
            ) {
                return $name;
            }
        }

        return null;
    }

    private function checkWrappedType(WrappedType $other): bool
    {
        if ($this->envRef === null) {
            return true;
        }

        return $this->tryGetMissingMethod($other) === null;
    }
}
