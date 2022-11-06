<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\GoType\GoType;

/**
 * fixme: merge with addressable
 *
 * @template T backed PHP value
 * @template H hash of a backed value
 * @template-extends GoValue<T>
 */
interface NonRefValue extends GoValue
{
    public static function create(mixed $value): self;

    /**
     * @return H
     */
    public function hash(): mixed;

    // fixme revisit arg
    public function reify(?GoType $with = null): self;
}
