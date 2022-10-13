<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoType\GoType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\WrappedValue;

/**
 * Alias for `null` in place of `nil` value in reference types.
 *
 * @internal
 */
const NIL = null;

/**
 * Value normalization for wrapped values.
 *
 * @internal
 */
function normalize_value(GoValue $value): GoValue
{
    if ($value instanceof WrappedValue) {
        $value = $value->unwind();
    }

    return $value;
}

/**
 * Type normalization for wrapped types.
 *
 * @internal
 */
function normalize_type(GoType $type): GoType
{
    if ($type instanceof WrappedType) {
        $type = $type->unwind();
    }

    return $type;
}
