<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntValue;

use GoPhp\GoValue\Slice\SliceValue;

use function GoPhp\assert_arg_type;
use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#copy
 */
class Copy implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(GoValue ...$argv): IntValue
    {
        assert_argc($this, $argv, 2);
        assert_arg_value($argv[0], SliceValue::class, 'slice', 1);
        assert_arg_type($argv[1], $argv[0]->type(), 1);

        /** @var array{SliceValue, SliceValue} $argv */
        $values = $argv[1]->unwrap();

        for ($i = 0, $until = $argv[0]->len(); $i < $until; ++$i) {
            $argv[0]->setBlindly($values[$i], $i);
        }

        // fixme
        // As a special case, if the destination's core type is []byte, copy also accepts a source argument with core type bytestring.
        // This form copies the bytes from the byte slice or string into the byte slice.

        return new IntValue($i);
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
