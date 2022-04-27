<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;
use function GoPhp\assert_types_compatible_with_cast;

final class SliceBuilder
{
    private function __construct(
        private readonly SliceType $type,
        private array $values,
    ) {}

    public static function fromType(SliceType $type): self
    {
        return new self($type, []);
    }

    public static function fromValue(SliceValue $value): self
    {
         return new self($value->type, $value->values);
    }

    public function push(GoValue $value): void
    {
        assert_types_compatible_with_cast($this->type->internalType, $value);

        $this->values[] = $value;
    }

    /**
     * Types must be checked beforehand.
     */
    public function pushBlindly(GoValue $value): void
    {
        $this->values[] = $value;
    }

    public function build(): SliceValue
    {
        return new SliceValue($this->values, $this->type);
    }
}
