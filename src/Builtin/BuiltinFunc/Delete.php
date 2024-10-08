<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\Hashable;
use GoPhp\GoValue\Map\MapValue;
use GoPhp\GoValue\VoidValue;

use function GoPhp\assert_arg_value;
use function GoPhp\assert_argc;

/**
 * @see https://pkg.go.dev/builtin#delete
 */
class Delete implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
    ) {}

    public function __invoke(Argv $argv): VoidValue
    {
        assert_argc($this, $argv, 2);
        assert_arg_value($argv[0], MapValue::class);

        $m = $argv[0]->value;
        $key = $argv[1]->value;

        /** @psalm-var Hashable $key */
        $m->delete($key);

        return VoidValue::get();
    }

    public function name(): string
    {
        return $this->name;
    }
}
