<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\Invokable;

interface BuiltinFunc extends Invokable
{
    public function name(): string;

    public function expectsTypeAsFirstArg(): bool;

    public function permitsStringUnpacking(): bool;
}
