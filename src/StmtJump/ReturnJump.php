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
    public const LEN_VOID = 0;
    public const LEN_SINGLE = 1;

    /**
     * @param V $value
     */
    private function __construct(
        public readonly GoValue $value,
        public readonly int $len,
    ) {}

    /**
     * @return self<VoidValue>
     */
    public static function fromVoid(): self
    {
        return new self(VoidValue::get(), self::LEN_VOID);
    }

    /**
     * @return self<GoValue>
     */
    public static function fromSingle(GoValue $value): self
    {
        return new self($value, self::LEN_SINGLE);
    }

    /**
     * @return self<TupleValue>
     */
    public static function fromTuple(TupleValue $tuple): self
    {
        return new self($tuple, $tuple->len);
    }

    /**
     * @param list<GoValue> $values
     * @return self<TupleValue>
     */
    public static function fromMultiple(array $values): self
    {
        $tuple = new TupleValue($values);

        return self::fromTuple($tuple);
    }

    /**
     * @return V[]
     */
    public function values(): array
    {
        return match ($this->len) {
            self::LEN_VOID => [],
            self::LEN_SINGLE => [$this->value],
            default => $this->value->unwrap(),
        };
    }
}
