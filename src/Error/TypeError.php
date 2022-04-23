<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
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

    public static function conversionError(GoValue $value, GoType $type): self
    {
        return new self(
            \sprintf(
                'cannot convert %s (%s) to type %s',
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
