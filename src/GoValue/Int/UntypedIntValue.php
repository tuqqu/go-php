<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\Error\ProgramError;
use GoPhp\GoType\UntypedType;

final class UntypedIntValue extends BaseIntValue
{
    public static function fromString(string $digits): self
    {
        $prefix = \substr($digits, 0, 2);

        $int = match (true) {
            '0b' === $prefix => \bindec(self::getDigits($digits, 2, 'binary')),
            '0x' === $prefix => \hexdec(self::getDigits($digits, 2, 'hexadecimal')),
            '0o' === $prefix => \octdec(self::getDigits($digits, 2, 'octal')),
            $digits[0] === '0' && \strlen($digits) >= 2 => \octdec(self::getDigits($digits, 1, 'octal')),
            default => (int) $digits,
        };

        return new self($int);
    }

    public function type(): UntypedType
    {
        return UntypedType::UntypedInt;
    }

    private static function getDigits(string $number, int $start, string $type): string
    {
        $digits = \substr($number, $start);

        return empty($digits) ?
            throw new ProgramError(\sprintf('%s literal has no digits', $type)) :
            $digits;
    }
}
