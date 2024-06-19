<?php

declare(strict_types=1);

namespace GoPhp\Debug;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BuiltinFuncValue;
use GoPhp\InvokableCall;

final class CallStackCollectorDebugger implements Debugger
{
    public const int DEFAULT_STACK_TRACE_DEPTH = 128;

    private readonly CallStack $callStack;

    public function __construct(int $maxTraceDepth = self::DEFAULT_STACK_TRACE_DEPTH)
    {
        $this->callStack = new CallStack($maxTraceDepth);
    }

    public function addStackTrace(InvokableCall $call): void
    {
        $name = match (true) {
            $call->func instanceof AddressableValue => $call->func->getQualifiedName(),
            $call->func instanceof BuiltinFuncValue => $call->func->getName(),
            default => throw new InternalError(sprintf('unknown call stack value %s', $call->func::class)),
        };

        $this->callStack->add(new CallTrace($name, $call->getPosition()));
    }

    public function releaseLastStackTrace(): void
    {
        $this->callStack->pop();
    }

    public function getCallStack(): CallStack
    {
        return $this->callStack;
    }
}
