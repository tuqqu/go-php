<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\InternalError;

final class JumpStack
{
    /** @var JumpHandler[] */
    private array $stack = [];

    public function push(JumpHandler $jump): void
    {
        $this->stack[] = $jump;
    }

    public function peek(): JumpHandler
    {
        return $this->stack[\array_key_last($this->stack)] ?? throw new InternalError('jump stack underflow');
    }

    public function pop(): JumpHandler
    {
        return \array_pop($this->stack) ?? throw new InternalError('jump stack underflow');
    }
}
