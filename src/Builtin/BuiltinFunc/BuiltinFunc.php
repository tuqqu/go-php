<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\ConstInvokable;

interface BuiltinFunc extends ConstInvokable
{
    public function name(): string;
}
