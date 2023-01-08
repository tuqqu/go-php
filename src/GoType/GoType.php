<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\AddressableValue;

interface GoType
{
    public function name(): string;

    public function equals(self $other): bool;

    public function isCompatible(self $other): bool;

    public function reify(): self;

    public function zeroValue(): AddressableValue;

    public function convert(AddressableValue $value): AddressableValue;
}
