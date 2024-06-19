<?php

declare(strict_types=1);

namespace GoPhp\Debug;

use GoPhp\InvokableCall;

interface Debugger
{
    public function addStackTrace(InvokableCall $call): void;

    public function releaseLastStackTrace(): void;

    public function getCallStack(): CallStack;
}
