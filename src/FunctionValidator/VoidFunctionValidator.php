<?php

declare(strict_types=1);

namespace GoPhp\FunctionValidator;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\Func\Signature;

final class VoidFunctionValidator implements FunctionValidator
{
    public function __construct(
        private readonly string $funcName,
        private readonly ?string $packageName = null,
    ) {}

    public function funcName(): string
    {
        return $this->funcName;
    }

    public function forFunc(string $name, string $package): bool
    {
        if ($this->packageName !== null && $this->packageName !== $package) {
            return false;
        }

        return $this->funcName === $name;
    }

    public function validate(Signature $signature): void
    {
        if ($signature->arity !== 0 || $signature->returnArity !== 0) {
            throw new InternalError(\sprintf('func %s must have no arguments and no return values', $this->funcName));
        }
    }
}
