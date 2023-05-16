<?php

declare(strict_types=1);

namespace GoPhp;

final class InvokableCallList
{
    /**
     * @var list<InvokableCall>
     */
    private array $calls = [];

    public function add(InvokableCall $call): void
    {
        $this->calls[] = $call;
    }

    public function empty(): void
    {
        $this->calls = [];
    }

    /**
     * @return list<InvokableCall>
     */
    public function get(): array
    {
        return $this->calls;
    }
}
