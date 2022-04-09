<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;
use GoPhp\GoValue\Int\Iota;

interface BuiltinProvider
{
    /**
     * Returns pointer to the ordinal number iota value.
     */
    public function iota(): Iota;

    /**
     * Returns environment with predefined values (builtins).
     */
    public function env(): Environment;
}
