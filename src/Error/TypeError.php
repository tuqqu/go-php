<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\GoValue;

final class TypeError extends \RuntimeException
{
    public static function implicitConversionError(GoValue $value, GoType $type): self
    {
        return new self(
            \sprintf(
                'cannot use %s (%s) as %s value',
                $value->toString(),
                $value->type()->name(),
                $type->name(),
            )
        );
    }

    public static function expectedSliceInArgumentUnpacking(GoValue $value, FuncValue|BuiltinFuncValue $funcValue): self
    {
        return new self(
            \sprintf(
                'cannot use %s (%s of type %s) as type %s in argument to %s',
                $value->toString(),
                $value->isNamed() ? 'variable' : 'value',
                $value->type()->name(),
                $funcValue instanceof BuiltinFuncValue
                    ? '[]T (slice)'
                    : $funcValue->signature->params->params[$funcValue->signature->params->len - 1]->type->name(),
                $funcValue instanceof BuiltinFuncValue
                    ? 'builtin function'
                    : 'function',
            )
        );
    }

    public static function conversionError(GoValue $value, GoType $type): self
    {
        return new self(
            \sprintf(
                'cannot convert %s (%s) to %s',
                $value->toString(),
                $value->type()->name(),
                $type->name(),
            ),
        );
    }

    public static function incompatibleTypes(GoType $a, GoType $b): self
    {
        return new self(
            \sprintf(
                'Type "%s" cannot be compatible with type "%s"',
                $a->name(),
                $b->name(),
            )
        );
    }

    public static function valueOfWrongType(GoValue $value, GoType|string $expected): self
    {
        return new self(
            \sprintf(
                'Got value of type "%s", whilst expecting "%s"',
                $value->type()->name(),
                \is_string($expected) ? $expected : $expected->name(),
            )
        );
    }

    public static function onlyComparableToNil(GoValue $value): self
    {
        return new self(
            \sprintf(
                'Invalid operation. %s can only be compared to nil',
                $value->type()->name(),
            )
        );
    }
}
