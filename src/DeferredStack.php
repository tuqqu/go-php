<?php

declare(strict_types=1);

namespace GoPhp;

use function array_reverse;

final class DeferredStack
{
    /** @var InvokableCall[][] */
    private array $stack = [];
    private int $context = 0;

    public function newContext(): void
    {
        $this->stack[$this->context++] = [];
    }

    public function push(InvokableCall $fn): void
    {
        $this->stack[$this->context - 1][] = $fn;
    }

    /**
     * @return iterable<InvokableCall>
     */
    public function iter(): iterable
    {
        $defers = $this->stack[$this->context - 1] ?? [];

        unset($this->stack[$this->context - 1]);
        $this->context--;

        yield from array_reverse($defers);
    }
}
