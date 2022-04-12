<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

final class GotoValue implements StmtValue
{
    public function __construct(
        public readonly string $label,
    ) {}
}
