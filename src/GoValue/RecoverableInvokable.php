<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Invokable value that can be recovered from a panic.
 */
interface RecoverableInvokable extends Invokable
{
    public function zeroReturnValue(): GoValue;
}
