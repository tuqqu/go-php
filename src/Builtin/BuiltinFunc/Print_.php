<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\VoidValue;
use GoPhp\Stream\OutputStream;

use function implode;

/**
 * @see https://pkg.go.dev/builtin#print
 */
class Print_ implements BuiltinFunc
{
    public function __construct(
        private readonly string $name,
        private readonly OutputStream $stderr,
    ) {}

    public function __invoke(Argv $argv): VoidValue
    {
        $output = [];

        foreach ($argv->values as $arg) {
            $output[] = $arg->value->toString();
        }

        $this->stderr->write(implode('', $output));

        return new VoidValue();
    }

    public function name(): string
    {
        return $this->name;
    }
}
