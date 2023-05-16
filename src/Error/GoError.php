<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Lexer\Position;

interface GoError
{
    public function getPosition(): ?Position;

    public function getMessage(): string;
}
