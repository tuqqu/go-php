<?php

declare(strict_types=1);

namespace GoPhp\GoType;

/**
 * Primitive types
 */
interface BasicType extends GoType
{
    public function isFloat(): bool;

    public function isInt(): bool;

    public function isString(): bool;
}
