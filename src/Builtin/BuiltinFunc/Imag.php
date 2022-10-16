<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\Complex\BaseComplexValue;
use GoPhp\GoValue\Float\BaseFloatValue;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#imag
 */
class Imag extends BaseBuiltinFunc
{
    public function __invoke(Argv $argv): BaseFloatValue
    {
        assert_argc($this, $argv, 1);
        assert_arg_value($argv[0], BaseComplexValue::class, BaseComplexValue::NAME);

        return $argv[0]->value->imag();
    }
}
