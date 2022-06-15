<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\GoValue;

interface GoType
{
    public function name(): string;

    public function equals(self $other): bool;

    public function isCompatible(self $other): bool;

    public function reify(): self;

    public function defaultValue(): GoValue;

    public function convert(GoValue $value): GoValue;
}
