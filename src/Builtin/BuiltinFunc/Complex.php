<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\Complex\ComplexNumber;
use GoPhp\GoValue\Complex\Complex128Value;
use GoPhp\GoValue\Complex\Complex64Value;
use GoPhp\GoValue\Complex\UntypedComplexValue;
use GoPhp\GoValue\Float\Float32Value;
use GoPhp\GoValue\Float\Float64Value;

use function GoPhp\assert_arg_float;
use function GoPhp\assert_arg_type;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#complex
 */
class Complex extends BaseBuiltinFunc
{
    public function __invoke(Argv $argv): ComplexNumber
    {
        assert_argc($this, $argv, 2);

        [$real, $imag] = $argv;

        assert_arg_float($real);
        assert_arg_type($real, $real->value->type());

        [$real, $imag] = [$real->value, $imag->value];

        if ($real instanceof Float32Value || $imag instanceof Float32Value) {
            return new Complex64Value($real->unwrap(), $imag->unwrap());
        }

        if ($real instanceof Float64Value || $imag instanceof Float64Value) {
            return new Complex128Value($real->unwrap(), $imag->unwrap());
        }

        return new UntypedComplexValue($real->unwrap(), $imag->unwrap());
    }
}
