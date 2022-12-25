<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\GroupSpec;
use GoParser\Ast\Spec;
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
 * Version of the Runtime
 */
const VERSION = '0.2.0';

const INIT_FUNC_NAME = 'init';
const MAIN_FUNC_NAME = 'main';
const MAIN_PACK_NAME = 'main';

/**
 * Unwindable object normalization.
 *
 * @internal
 *
 * @template T of object
 * @template V of object
 *
 * @param T $object
 * @psalm-return ($object is Unwindable<V> ? V : T)
 */
function normalize_unwindable(object $object): object
{
    if ($object instanceof Unwindable) {
        $object = $object->unwind();
    }

    return $object;
}

/**
 * Iterator over Specs
 *
 * @internal
 *
 * @template S of Spec
 *
 * @param S|GroupSpec $spec
 * @return iterable<S>
 */
function iter_spec(Spec $spec): iterable
{
    $spec instanceof GroupSpec
        ? yield from $spec->specs
        : yield $spec;
}
