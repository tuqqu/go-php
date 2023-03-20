<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\UntypedType;

use function bindec;
use function hexdec;
use function mb_ord;
use function octdec;
use function strlen;
use function substr;

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
        $prefix = substr($digits, 0, 2);

        $int = (int) match (true) {
            '0b' === $prefix => bindec(self::getDigits($digits, 2, 'binary')),
            '0x' === $prefix => hexdec(self::getDigits($digits, 2, 'hexadecimal')),
            '0o' === $prefix => octdec(self::getDigits($digits, 2, 'octal')),
            $digits[0] === '0' && strlen($digits) >= 2 => octdec(self::getDigits($digits, 1, 'octal')),
            default => $digits,
        };

        return new self($int);
    }

    public static function fromRune(string $rune): self
    {
        return new self(mb_ord($rune), UntypedType::UntypedRune);
    }

    public function type(): UntypedType
    {
        return $this->type;
    }

    private static function getDigits(string $number, int $start, string $type): string
    {
        $digits = substr($number, $start);

        if (empty($digits)) {
            throw RuntimeError::invalidNumberLiteral($type);
        }

        return $digits;
    }
}
