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
 * @psalm-type MethodMap = array<string, FuncType>
 * @template-implements Hashable<string>
 */
final class InterfaceType implements Hashable, RefType
{
    /**
     * @param MethodMap $methods
     */
    private function __construct(
        private readonly array $methods,
        private readonly ?Environment $envRef,
    ) {}

    /**
     * @param MethodMap $methods
     */
    public static function withMethods(array $methods, Environment $envRef): self
    {
        return new self($methods, $envRef);
    }

    public static function any(): self
    {
        return new self([], null);
    }

    public function isAny(): bool
    {
        return empty($this->methods);
    }

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
        if ($other instanceof UntypedNilType) {
            return true;
        }

        if ($this->isAny()) {
            return true;
        }

        if (!$other instanceof self) {
            return false;
        }

        if ($this->noMissingMethods($other)) {
            return true;
        }

        return false;
    }

    public function isCompatible(GoType $other): bool
    {
        if ($this->isAny()) {
            return true;
        }

        return match (true) {
            $other instanceof UntypedNilType => true,
            $other instanceof WrappedType,
            $other instanceof self => $this->noMissingMethods($other),
            default => false,
        };
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

        if ($this->noMissingMethods($value->type())) {
            return $value;
        }

        throw InterfaceTypeError::cannotUseAsInterfaceType($value, $this);
    }

    public function hash(): string
    {
        return $this->name();
    }

    public function tryGetMissingMethod(GoType $other): ?string
    {
        if ($this->envRef === null) {
            return null;
        }

        foreach ($this->methods as $name => $method) {
            if (
                $other instanceof WrappedType
                && !$this->envRef->hasMethod($name, $other)
                && !$this->envRef->hasMethod($name, $other->underlyingType)
            ) {
                return $name;
            }

            if ($other instanceof self) {
                $hasMethod = isset($other->methods[$name]) && $method->equals($other->methods[$name]);

                if (!$hasMethod) {
                    return $name;
                }
            }
        }

        return null;
    }

    private function noMissingMethods(self|WrappedType $other): bool
    {
        return $this->tryGetMissingMethod($other) === null;
    }
}
