<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

interface EntryPointValidator
{
    public function isEntryPackage(string $package): bool;

    public function validate(string $func, Signature $signature): bool;
}
