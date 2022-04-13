<?php

declare(strict_types=1);

namespace GoPhp;

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
        return $this->stack[\array_key_last($this->stack)] ?? throw new \Exception('stack underflow');
    }

    public function pop(): JumpHandler
    {
        return \array_pop($this->stack) ?? throw new \Exception('stack underflow');
    }
}
