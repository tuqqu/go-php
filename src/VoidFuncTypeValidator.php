<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\ProgramError;
use GoPhp\GoType\FuncType;

final class VoidFuncTypeValidator implements FuncTypeValidator
{
    public function __construct(
        private readonly string $funcName,
        private readonly ?string $packageName = null,
    ) {}

    public function supports(string $name, string $package): bool
    {
        if ($this->packageName !== null && $this->packageName !== $package) {
            return false;
        }

        return $this->funcName === $name;
    }

    public function validate(FuncType $type): void
    {
        if ($type->arity !== 0 || $type->returnArity !== 0) {
            throw ProgramError::funcMustBeNoArgsVoid($this->funcName);
        }
    }

    public function targets(): string
    {
        return $this->funcName;
    }
}
