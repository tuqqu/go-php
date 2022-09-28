<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\VoidValue;
use GoPhp\Stream\OutputStream;

/**
 * @see https://pkg.go.dev/builtin#println
 */
class Println extends BaseBuiltinFunc
{
    public function __construct(
        string $name,
        private readonly OutputStream $stderr,
    ) {
        parent::__construct($name);
    }

    public function __invoke(GoValue ...$argv): VoidValue
    {
        $output = [];

        foreach ($argv as $argv) {
            $output[] = $argv->toString();
        }

        $this->stderr->writeln(\implode(' ', $output));

        return new VoidValue();
    }
}
