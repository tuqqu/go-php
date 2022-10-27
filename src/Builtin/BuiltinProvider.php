<?php

declare(strict_types=1);

namespace GoPhp\Builtin;

use GoPhp\Env\Environment;

interface BuiltinProvider
{
    /**
     * Returns pointer to the ordinal number iota value.
     */
    public function iota(): Iota;

    /**
     * Returns environment with predefined values: builtin functions, constants, vars.
     */
    public function env(): Environment;
}
