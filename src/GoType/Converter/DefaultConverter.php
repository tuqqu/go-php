<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\TypeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\WrappedValue;

final class DefaultConverter
{
    public static function convert(GoValue $value, GoType $type): GoValue
    {
        $fromType = $value->type();

        // fixme find more pretty solution
        while ($fromType instanceof WrappedType) {
            $fromType = $fromType->underlyingType;
        }

        while ($value instanceof WrappedValue) {
            $value = $value->underlyingValue;
        }

        return $type->equals($fromType) ?
            $value :
            throw TypeError::conversionError($value, $type);
    }
}
