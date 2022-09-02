<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoType\GoType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\WrappedValue;

function normalize_value(GoValue $value): GoValue
{
    if ($value instanceof WrappedValue) {
        $value = $value->unwind();
    }

    return $value;
}

function normalize_type(GoType $type): GoType
{
    if ($type instanceof WrappedType) {
        $type = $type->unwind();
    }

    return $type;
}
