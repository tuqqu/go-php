<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\RuntimeError;
use GoPhp\Error\InternalError;
use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Array\ArrayValue;

final class ArrayType implements GoType
{
    public readonly string $name;
    public readonly int $len;
    public readonly GoType $elemType;

    public function __construct(GoType $elemType, ?int $len)
    {
        $this->elemType = $elemType;

        if ($len !== null) {
            $this->finish($len);
        }
    }

    public function name(): string
    {
        if (!$this->isFinished()) {
            throw InternalError::unreachable('array type must be complete prior to usage');
        }

        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self
            && $this->elemType->equals($other->elemType)
            && $this->len === $other->len;
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): self
    {
        return $this;
    }

    public function defaultValue(): ArrayValue
    {
        $values = [];
        for ($i = 0; $i < $this->len; ++$i) {
            $values[] = $this->elemType->defaultValue();
        }

        return new ArrayValue($values, $this);
    }

    /**
     * @psalm-suppress InaccessibleProperty
     */
    public function finish(int $len): void
    {
        if ($this->isFinished()) {
            return;
        }

        $this->len = $len;

        if ($this->elemType instanceof self && !$this->elemType->isFinished()) {
            throw RuntimeError::unfinishedArrayTypeUse();
        }

        $this->name = \sprintf('[%d]%s', $this->len, $this->elemType->name());
    }

    private function isFinished(): bool
    {
        return isset($this->len, $this->name);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
