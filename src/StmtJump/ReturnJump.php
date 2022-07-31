<?php

declare(strict_types=1);

namespace GoPhp\StmtJump;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\VoidValue;

final class ReturnJump implements StmtJump
{
    private function __construct(
        public readonly GoValue $value,
        public readonly int $len,
    ) {}

    public static function fromVoid(): self
    {
        return new self(new VoidValue(), 0);
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

        return self::fromTuple($tuple);
    }

    /**
     * @return GoValue[]
     */
    public function values(): array
    {
        return match ($this->len) {
            0 => [],
            1 => [$this->value],
            default => $this->value->values,
        };
    }
}
