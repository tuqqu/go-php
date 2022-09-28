<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Sequence;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#len
 */
class Len extends BaseBuiltinFunc
{
    public function __invoke(GoValue ...$argv): IntValue
    {
        assert_argc($this, $argv, 1);
        assert_arg_value($argv[0], Sequence::class, 'slice, array, string, map', 1);

        return new IntValue($argv[0]->len());
    }
}
