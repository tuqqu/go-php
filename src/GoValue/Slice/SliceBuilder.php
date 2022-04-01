<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Slice;

use GoPhp\GoType\SliceType;
use GoPhp\GoValue\GoValue;
use function GoPhp\assert_types_compatible;

final class SliceBuilder
{
    private array $values = [];

    private function __construct(
        private readonly SliceType $type,
    ) {}

    public static function fromType(SliceType $type): self
    {
        return new self($type);
    }

    public function push(GoValue $value): void
    {
        assert_types_compatible($this->type->internalType, $value->type());

        $this->values[] = $value;
    }

    public function build(): SliceValue
    {
        return new SliceValue($this->values, $this->type);
    }
}
