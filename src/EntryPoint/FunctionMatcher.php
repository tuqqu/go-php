<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

interface FunctionMatcher
{
    public function matches(string $package, string $funcName, Signature $signature): bool;
}
