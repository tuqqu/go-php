<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;

use function GoPhp\try_unwind;

final class DefaultConverter
{
    public static function convert(AddressableValue $value, GoType $type): AddressableValue
    {
        $value = try_unwind($value);

        return $type->equals($value->type())
            ? $value
            : throw RuntimeError::conversionError($value, $value->type());
    }
}
