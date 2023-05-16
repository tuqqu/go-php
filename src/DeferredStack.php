<?php

declare(strict_types=1);

namespace GoPhp;

use function array_reverse;

final class DeferredStack
{
    /** @var array<int, InvokableCallList> */
    private array $stack = [];
    private int $context = 0;

    public function newContext(): void
    {
        $this->stack[$this->context++] = new InvokableCallList();
    }

    public function push(InvokableCall $fn): void
    {
        $this->stack[$this->context - 1]->add($fn);
    }

    /**
     * @return iterable<InvokableCall>
     */
    public function iter(): iterable
    {
        if (!isset($this->stack[$this->context - 1])) {
            return;
        }

        yield from array_reverse(
            $this->stack[$this->context - 1]->get()
        );

        unset($this->stack[$this->context - 1]);
        $this->context--;
    }
}
