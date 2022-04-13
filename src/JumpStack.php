<?php

declare(strict_types=1);

namespace GoPhp;

final class JumpStack
{
    /** @var LabelJump[] */
    private array $stack = [];

    public function push(LabelJump $jump): void
    {
        $this->stack[] = $jump;
    }

    public function peek(): LabelJump
    {
        return $this->stack[\array_key_last($this->stack)] ?? throw new \Exception('stack underflow');
    }

    public function pop(): LabelJump
    {
        return \array_pop($this->stack) ?? throw new \Exception('stack underflow');
    }
}
