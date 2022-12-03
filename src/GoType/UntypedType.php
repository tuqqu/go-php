<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\AddressableValue;

use function GoPhp\normalize_unwindable;

enum UntypedType implements BasicType
{
    case UntypedInt;        // bare int literals
    case UntypedRune;       // bare rune 'c' literals
    case UntypedFloat;      // bare float literals
    case UntypedRoundFloat; // bare float literals with a trailing .0
    case UntypedBool;       // bare bool literals
    case UntypedComplex;    // bare complex literals

    public function name(): string
    {
        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => 'untyped int',
            self::UntypedFloat,
            self::UntypedRoundFloat => 'untyped float',
            self::UntypedBool => 'untyped bool',
            self::UntypedComplex => 'untyped complex',
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
            self::UntypedFloat,
            self::UntypedRoundFloat => NamedType::Float32,
            self::UntypedBool => NamedType::Bool,
            self::UntypedComplex => NamedType::Complex128,
        };
    }

    public function isCompatible(GoType $other): bool
    {
        $other = normalize_unwindable($other);

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
                NamedType::Uintptr,
                NamedType::Complex64,
                NamedType::Complex128,
                self::UntypedComplex,
                self::UntypedRoundFloat => true,
                default => $this->equals($other),
            },
            self::UntypedFloat => match ($other) {
                self::UntypedRoundFloat,
                NamedType::Float32,
                NamedType::Float64,
                NamedType::Complex64,
                NamedType::Complex128,
                self::UntypedComplex => true,
                default => $this->equals($other),
            },
            self::UntypedRoundFloat => match ($other) {
                self::UntypedInt,
                self::UntypedRune,
                self::UntypedFloat,
                NamedType::Float32,
                NamedType::Float64,
                NamedType::Int,
                NamedType::Int8,
                NamedType::Int32,
                NamedType::Int64,
                NamedType::Uint,
                NamedType::Uint8,
                NamedType::Uint16,
                NamedType::Uint32,
                NamedType::Uint64,
                NamedType::Uintptr,
                NamedType::Complex64,
                NamedType::Complex128,
                self::UntypedComplex => true,
                default => $this->equals($other),
            },
            self::UntypedBool => match ($other) {
                NamedType::Bool => true,
                default => $this->equals($other),
            },
            self::UntypedComplex => match ($other) {
                NamedType::Complex64,
                NamedType::Complex128,
                self::UntypedFloat,
                self::UntypedInt => true,
                default => $this->equals($other),
            },
        };
    }

    public function isFloat(): bool
    {
        return match ($this) {
            self::UntypedFloat,
            self::UntypedRoundFloat => true,
            default => false,
        };
    }

    public function isInt(): bool
    {
        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => true,
            default => false,
        };
    }

    public function isString(): bool
    {
        return false; // fixme add untyped str
    }

    public function convert(AddressableValue $value): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function defaultValue(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
