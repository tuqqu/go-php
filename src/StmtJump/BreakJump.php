<?php

declare(strict_types=1);

namespace GoPhp\StmtJump;

final class BreakJump implements StmtJump
{
    public function __construct(
        public readonly ?string $label,
    ) {}
}
