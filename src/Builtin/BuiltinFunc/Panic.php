<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\Error\PanicError;

use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#panic
 */
class Panic extends BaseBuiltinFunc
{
    public function __invoke(Argv $argv): never
    {
        assert_argc($this, $argv, 1);
        $v = $argv[0];

        throw new PanicError($v->value);
    }
}
