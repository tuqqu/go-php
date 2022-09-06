<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\Error\OperationError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;

/**
 * @template V of GoValue
 */
final class MapLookupValue implements GoValue
{
    /**
     * @param V $value
     * @param (\Closure(): void)|null $mutationCallback
     */
    public function __construct(
        public readonly GoValue $value,
        public readonly BoolValue $ok,
        private readonly ?\Closure $mutationCallback = null,
    ) {}

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function operate(Operator $op): GoValue
    {
       if ($op === Operator::BitAnd) {
            throw OperationError::cannotTakeAddressOfMapValue($this->value->type());
       }

       return $this->value->operate($op);
    }

    public function operateOn(Operator $op, GoValue $rhs): GoValue
    {
        return $this->value->operateOn($op, $rhs);
    }

    public function equals(GoValue $rhs): BoolValue
    {
        return $this->value->equals($rhs);
    }

    public function mutate(Operator $op, GoValue $rhs): void
    {
        $this->value->mutate($op, $rhs);

        if ($this->mutationCallback !== null) {
            ($this->mutationCallback)();
        }
    }

    public function copy(): GoValue
    {
        return $this->value->copy();
    }

    /**
     * @return V
     */
    public function unwrap(): GoValue
    {
        return $this->value;
    }

    public function type(): GoType
    {
        return $this->value->type();
    }
}
