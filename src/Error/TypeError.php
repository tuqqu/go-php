<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\ValueType;

final class TypeError extends \RuntimeException
{
    public static function conversionError(ValueType $from, ValueType $to): self
    {
        return new self(
            \sprintf(
                'Value of type "%s" cannot be converted to "%s"',
                $from->name(),
                $to->name(),
            )
        );
    }

    public static function incompatibleTypes(ValueType $a, ValueType $b): self
    {
        return new self(
            \sprintf(
                'Type "%s" cannot be compatible with type "%s"',
                $a->name(),
                $b->name(),
            )
        );
    }
}
