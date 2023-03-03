<?php

declare(strict_types=1);

namespace GoPhp;

enum JumpStatus
{
    case Goto;
    case Break;
    case Continue;

    public function isLoopJump(): bool
    {
        return $this !== self::Goto;
    }
}
