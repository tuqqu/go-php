<?php

declare(strict_types=1);

namespace GoPhp\Debug;

use GoParser\Lexer\Position;

final class CallTrace
{
    public function __construct(
        public readonly string $name,
        public readonly ?Position $position,
    ) {}
}
