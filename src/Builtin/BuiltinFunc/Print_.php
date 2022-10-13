<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

use GoPhp\Argv;
use GoPhp\GoValue\VoidValue;
use GoPhp\Stream\OutputStream;

/**
 * @see https://pkg.go.dev/builtin#print
 */
class Print_ extends BaseBuiltinFunc
{
    public function __construct(
        string $name,
        private readonly OutputStream $stderr,
    ) {
        parent::__construct($name);
    }

    public function __invoke(Argv $argv): VoidValue
    {
        $output = [];

        foreach ($argv->values as $arg) {
            $output[] = $arg->value->toString();
        }

        $this->stderr->write(\implode('', $output));

        return new VoidValue();
    }
}
