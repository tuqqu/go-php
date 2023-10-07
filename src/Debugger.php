<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\PanicError;

interface Debugger
{
    public function addStackTrace(InvokableCall|PanicError $call): void;

    /**
     * @return list<InvokableCall|PanicError>
     */
    public function getStackTrace(): array;
}
