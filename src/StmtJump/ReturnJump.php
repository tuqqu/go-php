<?php

declare(strict_types=1);

namespace GoPhp\StmtJump;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\TupleValue;
use GoPhp\GoValue\VoidValue;

/**
 * @template V of GoValue
 */
final class ReturnJump implements StmtJump
{
    private function __construct(
        public readonly GoValue $value,
        public readonly int $len,
    ) {}

    /**
     * @return self<VoidValue>
     */
    public static function fromVoid(): self
    {
        return new self(new VoidValue(), 0);
    }

    /**
     * @return self<TupleValue>
     */
    public static function fromTuple(TupleValue $tuple): self
    {
        return new self($tuple, $tuple->len);
    }

    /**
     * @return self<GoValue>
     */
    public static function fromSingle(GoValue $value): self
    {
        return new self($value, 1);
    }

    /**
     * @param GoValue[] $values
     * @return self<TupleValue>
     */
    public static function fromMultiple(array $values): self
    {
        $tuple = new TupleValue($values);

        return self::fromTuple($tuple);
    }

    /**
     * @return GoValue[]
     * @psalm-suppress NoInterfaceProperties
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
