<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoType\GoType;

/**
 * @template T = string|null
 */
final class Param
{
    /**
     * @param T $name
     */
    public function __construct(
        public readonly GoType $type,
        public readonly ?string $name = null,
        public readonly bool $variadic = false,
    ) {}

    public function equals(self $other): bool
    {
        return $this->variadic === $other->variadic && $this->type->equals($other->type);
    }
}
