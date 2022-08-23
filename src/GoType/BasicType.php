<?php

declare(strict_types=1);

namespace GoPhp\GoType;

interface BasicType extends GoType
{
    public function isFloat(): bool;

    public function isInt(): bool;
}
