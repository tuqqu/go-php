<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Int;

use GoPhp\Error\RuntimeError;

use function bindec;
use function hexdec;
use function octdec;
use function strlen;
use function substr;

enum IntSystem
{
    case Binary;
    case Octal;
    case OldOctal;
    case Hexadecimal;
    case Decimal;

    public static function fromPrefix(string $digits): self
    {
        return match (substr($digits, 0, 2)) {
            '0b' => self::Binary,
            '0o' => self::Octal,
            '0x' => self::Hexadecimal,
            default => match (true) {
                $digits[0] === '0' && strlen($digits) >= 2 => self::OldOctal,
                default => self::Decimal,
            },
        };
    }

    public function getDigits(string $number): int
    {
        $callback = match ($this) {
            self::Binary => bindec(...),
            self::Octal,
            self::OldOctal => octdec(...),
            self::Hexadecimal => hexdec(...),
            self::Decimal => null,
        };

        if ($callback === null) {
            return (int) $number;
        }

        $digits = substr($number, $this->prefixLen());

        if (empty($digits)) {
            throw RuntimeError::invalidNumberLiteral($this->name());
        }

        return (int) $callback($digits);
    }

    private function name(): string
    {
        return match ($this) {
            self::Binary => 'binary',
            self::Octal,
            self::OldOctal => 'octal',
            self::Hexadecimal => 'hexadecimal',
            self::Decimal => 'decimal',
        };
    }

    private function prefixLen(): int
    {
        return match ($this) {
            self::Binary,
            self::Hexadecimal,
            self::Octal => 2,
            self::OldOctal => 1,
            self::Decimal => 0,
        };
    }
}
