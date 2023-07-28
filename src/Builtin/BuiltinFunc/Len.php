<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Sequence;

use function GoPhp\assert_arg_value;
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
        assert_arg_value($argv[0], Sequence::class, 'slice, array, string, map');

        $v = $argv[0]->value;

        return new IntValue($v->len());
    }

    public function name(): string
    {
        return $this->name;
    }
}
