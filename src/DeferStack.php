<?php

declare(strict_types=1);

namespace GoPhp;

final class DeferStack
{
    /** @var callable[][] */
    private array $stack = [];
    private int $pos = 0;

    public function newContext(): void
    {
        $this->stack[$this->pos++] = [];
    }

    public function push(callable $fn): void
    {
        $this->stack[$this->pos - 1][] = $fn;
    }

    /**
     * @return callable[]
     */
    public function pop(): array
    {
        $defers = $this->stack[$this->pos - 1] ?? [];
        unset($this->stack[$this->pos - 1]);
        $this->pos--;

        return \array_reverse($defers);
    }
}
