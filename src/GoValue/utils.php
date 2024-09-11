<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use function spl_object_id;
use function dechex;

/**
 * Alias for `null` in place of `nil` value in reference types.
 *
 * @internal
 */
const NIL = null;

/**
 * Default address for values of reference types
 *
 * @internal
 */
const ZERO_ADDRESS = 0x0;

/**
 * Get the address of an object.
 *
 * @internal
 */
function get_address(Ref $refValue): string
{
    $address = $refValue->isNil()
        ? ZERO_ADDRESS
        : spl_object_id($refValue);

    return '0x' . dechex($address);
}
