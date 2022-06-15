<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\DefinitionError;
use GoPhp\Error\InternalError;
use GoPhp\Error\TypeError;
use GoPhp\GoValue\Array\ArrayValue;
use GoPhp\GoValue\GoValue;

final class ArrayType implements GoType
{
    public readonly string $name;
    public readonly int $len;
    public readonly GoType $elemType;

    public function __construct(GoType $elemType, ?int $len)
    {
        $this->elemType = $elemType;

        if ($len !== null) {
            $this->finish($len);
        }
    }

    public function name(): string
    {
        if ($this->isUnfinished()) {
            throw new InternalError('Array type must be complete prior to usage');
        }

        return $this->name;
    }

    public function equals(GoType $other): bool
    {
        return $other instanceof self
            && $this->elemType->equals($other->elemType)
            && $this->len === $other->len;
    }

    public function isCompatible(GoType $other): bool
    {
        return $this->equals($other);
    }

    public function reify(): static
    {
        return $this;
    }

    public function defaultValue(): GoValue
    {
        $values = [];
        for ($i = 0; $i < $this->len; ++$i) {
            $values[] = $this->elemType->defaultValue();
        }

        return new ArrayValue($values, $this);
    }

    /**
     * @psalm-suppress InaccessibleProperty
     */
    public function finish(int $len): void
    {
        if (!$this->isUnfinished()) {
            return;
        }

        $this->len = $len;

        if (
            $this->elemType instanceof self
            && $this->elemType->isUnfinished()
        ) {
            throw DefinitionError::unfinishedArrayTypeUse();
        }

        $this->name = \sprintf('[%d]%s', $this->len, $this->elemType->name());
    }

    private function isUnfinished(): bool
    {
        return !isset($this->len, $this->name);
    }

    public function convert(GoValue $value): GoValue
    {
        return $this->equals($value->type()) ?
            $value :
            throw TypeError::conversionError($value, $this);
    }
}
