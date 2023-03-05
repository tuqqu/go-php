<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\GroupSpec;
use GoParser\Ast\Spec;
use GoPhp\Env\EnvMap;
use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoValue\Unwindable;

/**
 * Version of the Runtime
 */
const VERSION = '0.2.0';

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

/**
 * Reify untyped type to named type.
 *
 * @internal
 */
function reify_untyped(GoType $type): GoType
{
    return match ($type) {
        UntypedType::UntypedInt => NamedType::Int,
        UntypedType::UntypedRune => NamedType::Rune,
        UntypedType::UntypedFloat,
        UntypedType::UntypedRoundFloat => NamedType::Float32,
        UntypedType::UntypedBool => NamedType::Bool,
        UntypedType::UntypedComplex => NamedType::Complex128,
        UntypedType::UntypedString => NamedType::String,
        default => $type,
    };
}
