<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\ProgramError;
use GoPhp\GoValue\GoValue;

enum UntypedType implements BasicType
{
    case UntypedInt;
    case UntypedRune; // bare rune 'c' literals
    case UntypedFloat;
    case UntypedBool;

    public function name(): string
    {
        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => 'untyped int',
            self::UntypedFloat => 'untyped float',
            self::UntypedBool => 'untyped bool',
        };
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
    }

    public function reify(): BasicType
    {
        return match ($this) {
            self::UntypedInt => NamedType::Int,
            self::UntypedRune => NamedType::Rune,
            self::UntypedFloat => NamedType::Float32,
            self::UntypedBool => NamedType::Bool,
        };
    }

    public function defaultValue(): never
    {
        throw new \UnhandledMatchError('not impls def val');
    }

    public function isCompatible(GoType $other): bool
    {
        if (!$other instanceof BasicType) {
            return false;
        }

        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => match ($other) {
                NamedType::Int,
                NamedType::Int8,
                NamedType::Int32,
                NamedType::Int64,
                NamedType::Uint,
                NamedType::Uint8,
                NamedType::Uint16,
                NamedType::Uint32,
                NamedType::Uint64,
                NamedType::Uintptr, => true,
                default => $this->equals($other),
            },
            self::UntypedFloat => match ($other) {
                NamedType::Float32,
                NamedType::Float64 => true,
                default => $this->equals($other),
            },
            self::UntypedBool => $this->equals($other)
        };
    }

    public function convert(GoValue $value): GoValue
    {
        throw new ProgramError('cannot convert to untyped type');
    }
}
