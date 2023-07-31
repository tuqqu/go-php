<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invokable;
use GoPhp\GoValue\RecoverableInvokable;

final class InvokableCall
{
    public function __construct(
        private readonly Invokable $func,
        private readonly Argv $argv,
    ) {}

    public function __invoke(): GoValue
    {
        return ($this->func)($this->argv);
    }

    public function tryRecover(): ?GoValue
    {
        return $this->func instanceof RecoverableInvokable
            ? $this->func->zeroReturnValue()
            : null;
    }
}
