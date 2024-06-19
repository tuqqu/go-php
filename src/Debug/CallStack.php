<?php

declare(strict_types=1);

namespace GoPhp\Debug;

use function array_pop;
use function array_reverse;
use function array_shift;
use function count;

final class CallStack
{
    /**
     * @var list<CallTrace>
     */
    private array $callTraces = [];

    public function __construct(
        private readonly int $limit,
    ) {}

    public function add(CallTrace $callTrace): void
    {
        if (count($this->callTraces) > $this->limit) {
            array_shift($this->callTraces);
        }

        $this->callTraces[] = $callTrace;
    }

    public function pop(): void
    {
        array_pop($this->callTraces);
    }

    /**
     * @return list<CallTrace>
     */
    public function getCallTraces(): array
    {
        return array_reverse($this->callTraces);
    }

    public function count(): int
    {
        return count($this->callTraces);
    }
}
