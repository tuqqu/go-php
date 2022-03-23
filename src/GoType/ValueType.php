<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;

interface ValueType
{
    public function name(): string;

    public function equals(self $other): bool;

    public function conforms(self $other): bool;

    public function reify(): static;

    public function defaultValue(): GoValue;
}
