<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Error\OperationError;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Slice\SliceValue;

use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#cap
 */
class Cap extends BaseBuiltinFunc
{
    public function __invoke(GoValue ...$argv): IntValue
    {
        assert_argc($this, $argv, 1);

        $value = $argv[0];

        if ($value instanceof ArrayValue) {
            return new IntValue($value->len());
        }

        if ($value instanceof SliceValue) {
            return new IntValue($value->cap());
        }

        throw OperationError::wrongArgumentType($value->type(), 'slice, array', 1);
    }
}
