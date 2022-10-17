<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\Complex\ComplexNumber;
use GoPhp\GoValue\Float\FloatNumber;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#imag
 */
class Imag extends BaseBuiltinFunc
{
    public function __invoke(Argv $argv): FloatNumber
    {
        assert_argc($this, $argv, 1);
        assert_arg_value($argv[0], ComplexNumber::class, ComplexNumber::NAME);

        return $argv[0]->value->imag();
    }
}
