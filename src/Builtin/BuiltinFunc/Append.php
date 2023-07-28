<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\Builtin\BuiltinFunc\Marker\PermitsStringUnpacking;
use GoPhp\GoValue\Slice\SliceValue;

use function array_slice;
use function GoPhp\assert_arg_type_for;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 *
 * As a special case, it is legal to append a string to a byte slice, like this:
 * ```
 * slice = append([]byte("hello "), "world"...)
 * ```
 *
 * @see https://pkg.go.dev/builtin#append
 */
class Append implements BuiltinFunc, PermitsStringUnpacking
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function __invoke(Argv $argv): SliceValue
    {
        assert_argc($this, $argv, 2, true);
        assert_arg_value($argv[0], SliceValue::class, SliceValue::NAME);

        /** @var SliceValue $slice */
        $slice = $argv[0]->value->clone();
        $elems = array_slice($argv->values, 1);

        foreach ($elems as $elem) {
            assert_arg_type_for($elem->value, $slice->type->elemType, $this->name);

            $slice->append($elem->value);
        }

        return $slice;
    }
}
