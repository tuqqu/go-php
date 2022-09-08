<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\ProgramError;
use GoPhp\GoType\FuncType;

interface FuncTypeValidator
{
    /**
     * Whether the validator supports given Qualified Name or nor
     */
    public function supports(string $name, string $package): bool;

    /**
     * Throws on validation error
     *
     * @throws ProgramError
     */
    public function validate(FuncType $type): void;

    /**
     * Returns function name, which it targets
     */
    public function targets(): string;
}
