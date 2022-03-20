<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Lexer\Position;

interface PositionAwareError
{
    public function getPosition(): Position;
}
