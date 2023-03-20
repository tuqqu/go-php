<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Error\InternalError;
use GoPhp\Operator;

use function count;

/**
 * Not a real Go value, but an internal representation
 * of a set of values returned from a function call with multiple return values.
 *
 * @psalm-type Tuple = list<GoValue>
 * @template-implements GoValue<Tuple>
 */
final class TupleValue implements GoValue
{
    public readonly int $len;

    /**
     * @param Tuple $values
     */
    public function __construct(
        public readonly array $values,
    ) {
        $this->len = count($this->values);
    }

    public function toString(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operate(Operator $op): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function operateOn(Operator $op, GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function mutate(Operator $op, GoValue $rhs): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function copy(): never
    {
        throw InternalError::unreachableMethodCall();
    }

    /**
     * @return Tuple
     */
    public function unwrap(): array
    {
        return $this->values;
    }

    public function type(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
