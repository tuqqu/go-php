<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\RuntimeError;
use GoPhp\Error\InternalError;
use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Array\ArrayValue;

/**
 * @see https://golang.org/ref/spec#Array_types
 */
final class ArrayType implements GoType
{
    public readonly GoType $elemType;
    private ?int $len;
    private ?string $name = null;

    private function __construct(GoType $elemType, ?int $len)
    {
        $this->elemType = $elemType;
        $this->len = $len;
    }

    public static function fromLen(GoType $elemType, int $len): self
    {
        return new self($elemType, $len);
    }

    public static function unfinished(GoType $elemType): self
    {
        return new self($elemType, null);
    }

    public function name(): string
    {
        if (!$this->isFinished()) {
            throw InternalError::unreachable('array type must be complete prior to usage');
        }

        return $this->name ??= \sprintf('[%d]%s', $this->len, $this->elemType->name());
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

    public function zeroValue(): ArrayValue
    {
        $values = [];
        for ($i = 0; $i < $this->len; ++$i) {
            $values[] = $this->elemType->zeroValue();
        }

        return new ArrayValue($values, $this);
    }

    public function finish(int $len): void
    {
        if ($this->isFinished()) {
            return;
        }

        if ($this->elemType instanceof self && !$this->elemType->isFinished()) {
            throw RuntimeError::unfinishedArrayTypeUse();
        }

        $this->len = $len;
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }

    /**
     * @psalm-assert !null $this->len
     */
    public function isFinished(): bool
    {
        return $this->len !== null;
    }

    public function getLen(): int
    {
        if (!$this->isFinished()) {
            throw InternalError::unreachable('array type must be complete prior to usage');
        }

        return $this->len;
    }
}
