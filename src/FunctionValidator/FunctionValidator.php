<?php

declare(strict_types=1);

namespace GoPhp\FunctionValidator;

use GoPhp\GoValue\Func\Signature;

interface FunctionValidator
{
    public function funcName(): string;

    public function forFunc(string $name, string $package): bool;

    public function validate(Signature $signature): void;
}
