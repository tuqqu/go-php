<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Debug\Debugger;
use GoPhp\Error\GoError;
use GoPhp\GoValue\GoValue;

final class RuntimeResult
{
    public function __construct(
        public readonly ExitCode $exitCode,
        public readonly ?GoValue $res,
        public readonly ?Debugger $debugger,
        public readonly ?GoError $error,
    ) {}
}
