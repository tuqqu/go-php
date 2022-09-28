<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\TypeValue;

/**
 * @template-extends Invokable<TypeValue|AddressableValue>
 */
interface BuiltinFunc extends Invokable
{
    public function name(): string;

    public function expectsTypeAsFirstArg(): bool;

    public function permitsStringUnpacking(): bool;
}
