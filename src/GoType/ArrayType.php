<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\GoValue;

final class ArrayType implements ValueType
{
    public readonly string $name;

    public function __construct(
        public readonly ValueType $internalType,
        public ?int $len,
    ) {
        if ($len !== null) {
            $this->setLen($len);
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(ValueType $other): bool
    {
        return $other instanceof self &&
            $this->internalType->equals($other->internalType) &&
            $this->len === $other->len;
    }

    public function conforms(ValueType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        $values = [];
        for ($i = 0; $i < $this->len; ++$i) {
            $values[] = $this->internalType->defaultValue();
        }

        return new ArrayValue($values, $this);
    }

    public function setLen(int $len): void
    {
        $this->len = $len;
        $this->name = \sprintf('[%d]%s', $this->len, $this->internalType->name());
    }

    public function isUnfinished(): bool
    {
        return $this->len === null;
    }
}
