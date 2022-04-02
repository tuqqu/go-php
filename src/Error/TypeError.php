<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

final class TypeError extends \RuntimeException
{
    public static function conversionError(GoType $from, GoType $to): self
    {
        return new self(
            \sprintf(
                'Value of type "%s" cannot be converted to "%s"',
                $from->name(),
                $to->name(),
            )
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
}
