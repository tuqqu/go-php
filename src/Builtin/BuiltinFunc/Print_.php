<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\Stream\OutputStream;

/**
 * @see https://pkg.go.dev/builtin#print
 */
class Print_ implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
        private readonly OutputStream $stderr,
    ) {}

    public function __invoke(GoValue ...$argv): VoidValue
    {
        $output = [];

        foreach ($argv as $value) {
            $output[] = $value->toString();
        }

        $this->stderr->write(\implode('', $output));

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
