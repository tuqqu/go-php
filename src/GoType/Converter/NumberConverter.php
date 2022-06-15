<?php

declare(strict_types=1);

namespace GoPhp\GoType\Converter;

use GoPhp\Error\TypeError;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;

final class NumberConverter
{
    public static function convert(GoValue $value, NamedType $type): SimpleNumber
    {
        return $value instanceof SimpleNumber ?
            $value->convertTo($type) :
            throw TypeError::conversionError($value, $type);
    }
}
