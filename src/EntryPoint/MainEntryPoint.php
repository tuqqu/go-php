<?php

declare(strict_types=1);

namespace GoPhp\EntryPoint;

use GoPhp\GoValue\Func\Signature;

final class MainEntryPoint implements EntryPointValidator
{
    private const FUNC_NAME = 'main';
    private const PACK_NAME = 'main';
    private const PARAM_COUNT = 0;
    private const RETURN_COUNT = 0;

    public function isEntryPackage(string $package): bool
    {
        return $package === self::PACK_NAME;
    }

    public function validate(string $func, Signature $signature): bool
    {
        return $func === self::FUNC_NAME
            && $signature->arity === self::PARAM_COUNT
            && $signature->returnArity === self::RETURN_COUNT;
    }
}
