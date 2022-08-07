<?php

declare(strict_types=1);

namespace GoPhp;

final class DeferStack
{
    /** @var callable[][] */
    private array $stack = [];
    private int $context = 0;

    public function newContext(): void
    {
        $this->stack[$this->context++] = [];
    }

    public function push(callable $fn): void
    {
        $this->stack[$this->context - 1][] = $fn;
    }

    /**
     * @return iterable<callable>
     */
    public function iter(): iterable
    {
        $defers = $this->stack[$this->context - 1] ?? [];
        unset($this->stack[$this->context - 1]);
        $this->context--;

        yield from \array_reverse($defers);
    }
}
