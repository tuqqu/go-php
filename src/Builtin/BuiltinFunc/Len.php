<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\ArrayType;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\PointerValue;
use GoPhp\GoValue\Sequence;

use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#len
 */
class Len implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(Argv $argv): IntValue
    {
        assert_argc($this, $argv, 1);

        $v = $argv[0]->value;

        if ($v instanceof PointerValue && ($v->type()->pointsTo instanceof ArrayType)) {
            if ($v->isNil()) {
                return new IntValue(0);
            }

            $v = $v->deref();
        }

        if (!$v instanceof Sequence) {
            throw RuntimeError::wrongArgumentTypeForBuiltin($v, $this->name);
        }

        return new IntValue($v->len());
    }

    public function name(): string
    {
        return $this->name;
    }
}
