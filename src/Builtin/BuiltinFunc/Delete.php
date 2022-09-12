<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
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

    public function __invoke(GoValue ...$argv): VoidValue
    {
        assert_argc($argv, 2);
        assert_arg_value($argv[0], MapValue::class, MapValue::NAME, 1);

        $argv[0]->delete($argv[1]);

        return new VoidValue();
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
