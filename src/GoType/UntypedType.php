<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\Error\InternalError;
use GoPhp\GoValue\GoValue;

use function GoPhp\normalize_type;

enum UntypedType implements BasicType
{
    case UntypedInt;
    case UntypedRune; // bare rune 'c' literals
    case UntypedFloat;
    case UntypedRoundFloat;
    case UntypedBool;

    public function name(): string
    {
        return match ($this) {
            self::UntypedInt,
            self::UntypedRune => 'untyped int',
            self::UntypedFloat,
            self::UntypedRoundFloat => 'untyped float',
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
            self::UntypedFloat,
            self::UntypedRoundFloat => NamedType::Float32,
            self::UntypedBool => NamedType::Bool,
        };
    }

    public function isCompatible(GoType $other): bool
    {
        $other = normalize_type($other);

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
                NamedType::Uintptr => true,
                default => $this->equals($other),
            },
            self::UntypedFloat => match ($other) {
                NamedType::Float32,
                NamedType::Float64 => true,
                default => $this->equals($other),
            },
            self::UntypedRoundFloat => match ($other) {
                NamedType::Float32,
                NamedType::Float64 => true,
                default => $this->isCompatible(self::UntypedInt),
            },
            self::UntypedBool => match ($other) {
                NamedType::Bool => true,
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

    public function convert(GoValue $value): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function defaultValue(): never
    {
        throw InternalError::unreachableMethodCall();
    }
}
