<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;

final class SliceType implements GoType
{
    public readonly string $name;

    public function __construct(
        public readonly GoType $internalType,
    ) {
        $this->name = \sprintf('[]%s', $this->internalType->name());
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self &&
            $this->internalType->equals($other->internalType);
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        //fixme nil
    }
}
