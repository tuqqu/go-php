<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;

use function GoPhp\assert_types_compatible_with_cast;

final class SliceBuilder
{
    private array $values = [];
    private ?int $cap = null;

    private function __construct(
        private readonly SliceType $type,
    ) {}

    public static function fromType(SliceType $type): self
    {
        return new self($type);
    }

    public function push(GoValue $value): void
    {
        assert_types_compatible_with_cast($this->type->elemType, $value);

        $this->values[] = $value;
    }

    public function setCap(int $cap): void
    {
        $this->cap = $cap;
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
        return new SliceValue(
            $this->values,
            $this->type,
            $this->cap,
        );
    }
}
