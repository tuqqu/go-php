<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

interface EntryPointValidator
{
    public function validate(string $package, string $func, Signature $signature): bool;
}
