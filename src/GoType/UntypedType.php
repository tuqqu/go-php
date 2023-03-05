<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\AddressableValue;

use function GoPhp\normalize_unwindable;

/**
 * Primitive type that is not yet assigned to any variable
 */
enum UntypedType implements BasicType
{
    case UntypedInt;        // bare int literals
    case UntypedRune;       // bare rune 'c' literals
    case UntypedFloat;      // bare float literals
    case UntypedRoundFloat; // bare float literals with a trailing .0
    case UntypedBool;       // bare bool literals
    case UntypedComplex;    // bare complex literals
    case UntypedString;     // bare string literals

    public function name(): string
    {
        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => 'untyped int',
            self::UntypedFloat,
            self::UntypedRoundFloat => 'untyped float',
            self::UntypedBool => 'untyped bool',
            self::UntypedComplex => 'untyped complex',
            self::UntypedString => 'untyped string',
        };
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
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
            self::UntypedString => match ($other) {
                NamedType::String => true,
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
        return $this === self::UntypedString;
    }

    public function convert(AddressableValue $value): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function zeroValue(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
