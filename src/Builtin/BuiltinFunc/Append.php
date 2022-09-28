<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Slice\SliceValue;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#append
 */
class Append extends BaseBuiltinFunc
{
    public function __invoke(GoValue ...$argv): SliceValue
    {
        assert_argc($this, $argv, 2, true);
        assert_arg_value($argv[0], SliceValue::class, SliceValue::NAME, 1);

        /** @var SliceValue $slice */
        $slice = $argv[0]->clone();
        $elems = \array_slice($argv, 1);

        foreach ($elems as $elem) {
            $slice->append($elem);
        }

        return $slice;
    }

    public function permitsStringUnpacking(): bool
    {
        // As a special case, it is legal to append a string to a byte slice, like this:
        // slice = append([]byte("hello "), "world"...)
        return true;
    }
}
