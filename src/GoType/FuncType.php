<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\GoValue;

final class FuncType implements GoType
{
    public readonly string $name;

    public function __construct(
        Params $params,
        Params $returns,
    )
    {
        $this->name = \sprintf(
            'func(%s)(%s)',
            self::paramsToString($params),
            self::paramsToString($returns)
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self && $this->name === $other->name;
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
