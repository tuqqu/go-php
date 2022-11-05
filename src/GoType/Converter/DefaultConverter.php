<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\GoValue;

use function GoPhp\normalize_unwindable;

final class DefaultConverter
{
    public static function convert(GoValue $value, GoType $type): AddressableValue
    {
        $value = normalize_unwindable($value);

        return $type->equals($value->type())
            ? $value
            : throw RuntimeError::conversionError($value, $value->type());
    }
}
