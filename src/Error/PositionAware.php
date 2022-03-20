<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Lexer\Position;

trait PositionAware
{
    public readonly Position $position;

    public function getPosition(): Position
    {
        return $this->position;
    }
}
