<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Lexer\Position;

trait PositionAwareTrait
{
    private ?Position $position = null;

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): void
    {
        $this->position = $position;
    }
}
