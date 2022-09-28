<?php

declare(strict_types=1);

namespace GoPhp\GoType;

interface BasicType extends GoType
{
    public function isFloat(): bool; //fixme check

    public function isInt(): bool;

    public function isString(): bool;
}
