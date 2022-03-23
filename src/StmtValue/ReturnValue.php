<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

use GoPhp\GoValue\GoValue;

final class ReturnValue implements StmtValue
{
    /**
     * @param GoValue[] $values
     */
    public function __construct(
        public readonly array $values,
    ) {}

    public function isNone(): bool
    {
        return false;
    }
}
