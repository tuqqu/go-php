<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\NilValue;

final class FuncType implements RefType
{
    public readonly string $name;

    public function __construct(Params $params, Params $returns)
    {
        $this->name = \sprintf('func(%s)(%s)', $params, $returns);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        // fixme probably need to save params and validate through them
        return $other instanceof self && $this->name === $other->name;
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof UntypedNilType || $this->equals($other);
    }

    public function reify(): self
    {
        return $this;
    }

    public function defaultValue(): NilValue
    {
        return new NilValue($this);
    }

    public function convert(GoValue $value): GoValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
