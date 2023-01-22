<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\GroupSpec;
use GoParser\Ast\Spec;
use GoPhp\Env\EnvMap;
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

/**
 * Default name of the main package
 *
 * @internal
 */
const MAIN_PACK_NAME = 'main';

/**
 * Default name of the main function
 *
 * @internal
 */
const MAIN_FUNC_NAME = 'main';

/**
 * Default name of the init function
 *
 * @internal
 */
const INIT_FUNC_NAME = 'init';

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

/**
 * @internal
 */
function construct_qualified_name(string $selector, string $namespace): string
{
    return $namespace === EnvMap::NAMESPACE_TOP
        ? $selector
        : $namespace . '.' . $selector;
}
