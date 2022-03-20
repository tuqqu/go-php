<?php

declare(strict_types=1);

namespace GoPhp;

enum StmtValue
{
    case None;
    case Break;
    case Continue;
    case Return;
    case Goto;

    public function isNone(): bool
    {
        return $this === self::None;
    }
}
