<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\PanicError;

use function count;
use function array_shift;

final class CallStackCollectorDebugger implements Debugger
{
    public const DEFAULT_STACK_TRACE_DEPTH = 128;

    /** @var list<InvokableCall|PanicError> */
    private array $stackTrace = [];

    public function __construct(
        private readonly bool $enableDebug = true,
        private readonly int $maxTraceDepth = self::DEFAULT_STACK_TRACE_DEPTH,
    ) {}

    public function addStackTrace(InvokableCall|PanicError $call): void
    {
        if (!$this->enableDebug) {
            return;
        }

        $this->stackTrace[] = $call;

        if (count($this->stackTrace) > $this->maxTraceDepth) {
            array_shift($this->stackTrace);
        }
    }

    /**
     * @return list<InvokableCall|PanicError>
     */
    public function getStackTrace(): array
    {
        return $this->stackTrace;
    }
}
