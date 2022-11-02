<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoValue\Unwindable;

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
 * Unwindable object normalization.
 *
 * @internal
 *
 * @template T of object
 * @template O as Unwindable<T>|object
 *
 * @param O $object
 * @psalm-return (O is Unwindable<T> ? T : object)
 */
function normalize_unwindable(object $object): object
{
    if ($object instanceof Unwindable) {
        $object = $object->unwind();
    }

    return $object;
}
