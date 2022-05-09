<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;

final class UntypedNilType implements RefType
{
    public function name(): string
    {
        return 'untyped nil';
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
    }

    public function reify(): RefType
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        throw new \Exception('cannot have def value');
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof RefType;
    }
}
