<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;

final class ReturnValue implements StmtValue
{
    private function __construct(
        public readonly ?GoValue $value,
        public readonly int $len,
    ) {}

    public static function fromVoid(): self
    {
        return new self(null, 0);
    }

    public static function fromTuple(TupleValue $tuple): self
    {
        return new self($tuple, $tuple->len);
    }

    public static function fromSingle(GoValue $value): self
    {
        return new self($value, 1);
    }

    /**
     * @param GoValue[] $values
     */
    public static function fromMultiple(array $values): self
    {
        $tuple = new TupleValue($values);

        return new self($tuple, $tuple->len);
    }
}
