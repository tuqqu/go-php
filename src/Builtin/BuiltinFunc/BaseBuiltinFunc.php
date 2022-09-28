<?php

declare(strict_types=1);

namespace GoPhp\Builtin\BuiltinFunc;

abstract class BaseBuiltinFunc implements BuiltinFunc
{
    public function __construct(
        protected readonly string $name,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function expectsTypeAsFirstArg(): bool
    {
        return false;
    }

    public function permitsStringUnpacking(): bool
    {
        return false;
    }
}
