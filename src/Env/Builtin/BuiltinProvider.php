<?php

declare(strict_types=1);

namespace GoPhp\Env\Builtin;

use GoPhp\Env\Environment;

interface BuiltinProvider
{
    public function provide(): Environment;
}
