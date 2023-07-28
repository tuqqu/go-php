<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\Error\RuntimeError;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Slice\SliceValue;

use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#cap
 */
class Cap implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(Argv $argv): IntValue
    {
        assert_argc($this, $argv, 1);

        $capable = $argv[0];

        if ($capable->value instanceof ArrayValue) {
            return new IntValue($capable->value->len());
        }

        if ($capable->value instanceof SliceValue) {
            return new IntValue($capable->value->cap());
        }

        throw RuntimeError::wrongArgumentType($capable, 'slice, array');
    }

    public function name(): string
    {
        return $this->name;
    }
}
