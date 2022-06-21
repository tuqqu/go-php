<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\WrappedValue;

final class DefaultConverter
{
    public static function convert(GoValue $value, GoType $type): GoValue
    {
        if ($value instanceof WrappedValue) {
            $value = $value->unwind();
        }

        return $type->equals($value->type()) ?
            $value :
            throw TypeError::conversionError($value, $value->type());
    }
}
