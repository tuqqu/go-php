<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\GoType\UntypedType;

use function mb_ord;

final class UntypedIntValue extends IntNumber
{
    public function __construct(
        int $value,
        private readonly UntypedType $type = UntypedType::UntypedInt,
    ) {
        parent::__construct($value);
    }

    public static function fromString(string $digits): self
    {
        return new self(IntSystem::fromPrefix($digits)->getDigits($digits));
    }

    public static function fromRune(string $rune): self
    {
        return new self(mb_ord($rune), UntypedType::UntypedRune);
    }

    public function type(): UntypedType
    {
        return $this->type;
    }
}
