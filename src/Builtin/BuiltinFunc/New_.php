<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\TypeValue;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#new
 */
class New_ extends BaseBuiltinFunc
{
    public function __invoke(GoValue ...$argv): PointerValue
    {
        assert_argc($this, $argv, 1);
        assert_arg_value($argv[0], TypeValue::class, 'type', 1);

        return PointerValue::fromValue($argv[0]->unwrap()->defaultValue());
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return true;
    }
}
