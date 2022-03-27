<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;

final class ReturnValue implements StmtValue
{
    private function __construct(
        public readonly ?TupleValue $values
    ) {}

    public static function fromVoid(): self
    {
        return new self(null);
    }

    public static function fromTuple(TupleValue $tuple): self
    {
        return new self($tuple);
    }

    /**
     * @param GoValue[] $values
     */
    public static function fromValues(array $values): self
    {
        return new self(new TupleValue($values));
    }

    public function isNone(): bool
    {
        return false;
    }
}
