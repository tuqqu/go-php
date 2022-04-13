<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

final class GotoValue extends \Exception implements StmtValue
{
    public function __construct(
        public readonly string $label,
    ) {}
}
