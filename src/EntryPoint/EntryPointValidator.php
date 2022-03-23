<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

interface EntryPointValidator
{
    public function validate(string $packageName, string $name, Signature $signature): bool;
}
