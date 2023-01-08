<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Func\Params;

final class FuncType implements RefType
{
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
    }

    public function name(): string
    {
        return \sprintf(
            'func(%s)%s',
            $this->params,
            match ($this->returns->len) {
                0 => '',
                1 => \sprintf(' %s', $this->returns),
                default => \sprintf(' (%s)', $this->returns),
            }
        );
    }

    public function equals(GoType $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        foreach ($this->params->iter() as $i => $param) {
            if (!$param->equals($other->params[$i])) {
                return false;
            }
        }

        foreach ($this->returns->iter() as $i => $param) {
            if (!$param->equals($other->returns[$i])) {
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

    public function zeroValue(): FuncValue
    {
        return FuncValue::nil($this);
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return DefaultConverter::convert($value, $this);
    }
}
