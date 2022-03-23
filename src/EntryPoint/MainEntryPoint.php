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

    public function validate(
        string $packageName,
        string $name,
        Signature $signature
    ): bool {
        return $name === self::FUNC_NAME &&
            $packageName === self::PACK_NAME &&
            $signature->arity === self::PARAM_COUNT &&
            $signature->returnArity === self::RETURN_COUNT;
    }
}
