<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\Env\Environment;
use GoPhp\PanicPointer;

interface BuiltinProvider
{
    /**
     * Returns pointer to the ordinal number iota value.
     */
    public function iota(): Iota;

    /**
     * Returns pointer to the panic value.
     */
    public function panicPointer(): PanicPointer;

    /**
     * Returns environment with predefined values: builtin functions, constants, vars.
     */
    public function env(): Environment;
}
