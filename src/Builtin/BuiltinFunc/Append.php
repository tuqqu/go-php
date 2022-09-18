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
class Append implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(GoValue ...$argv): SliceValue
    {
        assert_argc($this, $argv, 2, true);
        assert_arg_value($argv[0], SliceValue::class, SliceValue::NAME, 1);

        /** @var SliceValue $slice */
        $slice = $argv[0]->clone();

        // fixme
        //As a special case, it is legal to append a string to a byte slice, like this:
        //slice = append([]byte("hello "), "world"...)

        unset($argv[0]);
        foreach ($argv as $value) {
            $slice->append($value);
        }

        return $slice;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return false;
    }
}
