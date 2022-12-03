<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\GoType\NamedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\SimpleNumber;

final class NumberConverter
{
    public static function convert(AddressableValue $value, NamedType $type): AddressableValue
    {
        return $value instanceof SimpleNumber ?
            $value->convertTo($type) :
            DefaultConverter::convert($value, $type);
    }
}
