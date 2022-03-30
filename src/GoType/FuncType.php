<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;

final class FuncType implements ValueType
{
    public readonly string $fullType;

    public function __construct(
        Params $params,
        Params $returns,
    )
    {
        $this->fullType = \sprintf(
            'func(%s)(%s)',
            self::paramsToString($params),
            self::paramsToString($returns)
        );
    }

    public function name(): string
    {
        return $this->fullType;
    }

    public function equals(ValueType $other): bool
    {
        return $other instanceof self && $this->fullType === $other->fullType;
    }

    public function isCompatible(ValueType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        //fixme
    }

    private static function paramsToString(Params $params): string
    {
        $str = '';
        foreach ($params as $param) {
            $str .= $param->type->name();
            $str .= ','; //fixme comma
        }

        return $str;
    }
}
