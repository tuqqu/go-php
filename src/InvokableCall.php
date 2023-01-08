<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;

final class InvokableCall
{
    public function __construct(
        public readonly Invokable $func,
        public readonly Argv $argv,
    ) {}

    public function __invoke(): GoValue
    {
        return ($this->func)($this->argv);
    }
}
