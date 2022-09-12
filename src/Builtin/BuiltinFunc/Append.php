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
        assert_argc($argv, 1, variadic: true);
        assert_arg_value($argv[0], SliceValue::class, SliceValue::NAME, 1);

        /** @var SliceValue $slice */
        $slice = $argv[0]->clone();

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
