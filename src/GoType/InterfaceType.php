<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Env\Environment;
use GoPhp\Error\RuntimeError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Interface\InterfaceValue;
use GoPhp\GoValue\WrappedValue;

use function array_keys;
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
        return sprintf(
            '%s{%s}',
            InterfaceValue::NAME,
            implode(', ', array_keys($this->methods)),
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

        throw RuntimeError::conversionError($value, $value->type());
    }

    public function hash(): string
    {
        return $this->name();
    }

    private function checkWrappedType(WrappedType $other): bool
    {
        if ($this->envRef === null) {
            return true;
        }

        foreach ($this->methods as $name => $method) {
            if (
                !$this->envRef->hasMethod($name, $other)
                && !$this->envRef->hasMethod($name, $other->underlyingType)
            ) {
                return false;
            }
        }

        return true;
    }
}
