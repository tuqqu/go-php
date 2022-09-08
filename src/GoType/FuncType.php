<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;

final class FuncType implements RefType
{
    public readonly string $name;
    public readonly int $arity;
    public readonly int $returnArity;
    public readonly bool $variadic;
    public readonly bool $namedReturns;

    public function __construct(
        public readonly Params $params,
        public readonly Params $returns,
    ) {
        $this->arity = $this->params->len;
        $this->returnArity = $this->returns->len;
        $this->variadic = $this->params->variadic;
        $this->namedReturns = $this->returns->named;
        $this->name = \sprintf('func(%s)(%s)', $params, $returns);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        foreach ($this->params->iter() as $i => $param) {
            if (!$param->equals($other->params->params[$i])) {
                return false;
            }
        }

        foreach ($this->returns->iter() as $i => $param) {
            if (!$param->equals($other->returns->params[$i])) {
                return false;
            }
        }

        return true;
    }

    public function isCompatible(GoType $other): bool
    {
        return $other instanceof UntypedNilType || $this->equals($other);
    }

    public function reify(): self
    {
        return $this;
    }

    public function defaultValue(): FuncValue
    {
        return FuncValue::nil($this);
    }

    public function convert(GoValue $value): GoValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
