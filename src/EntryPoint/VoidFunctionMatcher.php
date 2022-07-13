<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

final class VoidFunctionMatcher implements FunctionMatcher
{
    public function __construct(
        private readonly string $packageName,
        private readonly string $funcName,
    ) {}

    public function matches(string $package, string $funcName, Signature $signature): bool
    {
        return $package === $this->packageName
            && $funcName === $this->funcName
            && $signature->arity === 0
            && $signature->returnArity === 0;
    }
}
