<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

final class LabelValue implements StmtValue
{
    public function __construct(
        public readonly string $label,
        public readonly StmtValue $stmtValue,
    ) {}
}
