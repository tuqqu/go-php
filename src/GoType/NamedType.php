<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoType\Converter\DefaultConverter;
use GoPhp\GoType\Converter\NumberConverter;
use GoPhp\GoType\Converter\StringConverter;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Complex\Complex128Value;
use GoPhp\GoValue\Complex\Complex64Value;
use GoPhp\GoValue\Float\Float32Value;
use GoPhp\GoValue\Float\Float64Value;
use GoPhp\GoValue\Int\Int16Value;
use GoPhp\GoValue\Int\Int32Value;
use GoPhp\GoValue\Int\Int64Value;
use GoPhp\GoValue\Int\Int8Value;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Uint16Value;
use GoPhp\GoValue\Int\Uint32Value;
use GoPhp\GoValue\Int\Uint64Value;
use GoPhp\GoValue\Int\Uint8Value;
use GoPhp\GoValue\Int\UintptrValue;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\GoValue\String\StringValue;

use function GoPhp\try_unwind;

/**
 * Named types are primitive types that have a name.
 */
enum NamedType: string implements BasicType
{
    public const self Rune = self::Int32;
    public const self Byte = self::Uint8;

    // signed integer types
    case Int = 'int';
    case Int8 = 'int8';
    case Int16 = 'int16';
    case Int32 = 'int32'; // Rune
    case Int64 = 'int64';

    // unsigned integer types
    case Uint = 'uint';
    case Uint8 = 'uint8'; // Byte
    case Uint16 = 'uint16';
    case Uint32 = 'uint32';
    case Uint64 = 'uint64';
    case Uintptr = 'uintptr';

    // floating point types
    case Float32 = 'float32';
    case Float64 = 'float64';

    // complex number types
    case Complex64 = 'complex64';
    case Complex128 = 'complex128';

    case Bool = 'bool';
    case String = 'string';

    public static function fromUntyped(UntypedType $type): self
    {
        return match ($type) {
            UntypedType::UntypedInt => self::Int,
            UntypedType::UntypedRune => self::Rune,
            UntypedType::UntypedFloat,
            UntypedType::UntypedRoundFloat => self::Float32,
            UntypedType::UntypedBool => self::Bool,
            UntypedType::UntypedComplex => self::Complex128,
            UntypedType::UntypedString => self::String,
        };
    }

    public function name(): string
    {
        return $this->value;
    }

    public function equals(GoType $other): bool
    {
        return $this === $other;
    }

    public function zeroValue(): AddressableValue
    {
        return match ($this) {
            self::Int => new IntValue(0),
            self::Int8 => new Int8Value(0),
            self::Int16 => new Int16Value(0),
            self::Int32 => new Int32Value(0),
            self::Int64 => new Int64Value(0),
            self::Uint => new UintValue(0),
            self::Uint8 => new Uint8Value(0),
            self::Uint16 => new Uint16Value(0),
            self::Uint32 => new Uint32Value(0),
            self::Uint64 => new Uint64Value(0),
            self::Uintptr => new UintptrValue(0),
            self::Float32 => new Float32Value(0.0),
            self::Float64 => new Float64Value(0.0),
            self::Complex64 => new Complex64Value(0.0, 0.0),
            self::Complex128 => new Complex128Value(0.0, 0.0),
            self::Bool => BoolValue::false(),
            self::String => new StringValue(''),
        };
    }

    public function isCompatible(GoType $other): bool
    {
        $other = try_unwind($other);

        if (!$other instanceof BasicType) {
            return false;
        }

        return match ($this) {
            self::Int,
            self::Int8,
            self::Int32,
            self::Int64,
            self::Uint,
            self::Uint8,
            self::Uint16,
            self::Uint32,
            self::Uint64,
            self::Uintptr => match ($other) {
                UntypedType::UntypedInt,
                UntypedType::UntypedRune,
                UntypedType::UntypedRoundFloat => true,
                default => $this->equals($other),
            },
            self::Float64,
            self::Float32 => match ($other) {
                UntypedType::UntypedInt,
                UntypedType::UntypedFloat,
                UntypedType::UntypedRoundFloat => true,
                default => $this->equals($other),
            },
            self::Complex64,
            self::Complex128 => match ($other) {
                UntypedType::UntypedComplex,
                UntypedType::UntypedFloat,
                UntypedType::UntypedInt => true,
                default => $this->equals($other),
            },
            self::Bool => match ($other) {
                UntypedType::UntypedBool => true,
                default => $this->equals($other),
            },
            self::String => match ($other) {
                UntypedType::UntypedString => true,
                default => $this->equals($other),
            },
            default => $this->equals($other),
        };
    }

    public function convert(AddressableValue $value): AddressableValue
    {
        return match ($this) {
            self::Int,
            self::Int8,
            self::Int32,
            self::Int64,
            self::Uint,
            self::Uint8,
            self::Uint16,
            self::Uint32,
            self::Uint64,
            self::Uintptr,
            self::Float64,
            self::Float32 => NumberConverter::convert($value, $this),
            self::String => StringConverter::convert($value),
            default => DefaultConverter::convert($value, $this),
        };
    }

    public function isFloat(): bool
    {
        return match ($this) {
            self::Float32,
            self::Float64 => true,
            default => false,
        };
    }

    public function isInt(): bool
    {
        return match ($this) {
            self::Int,
            self::Int8,
            self::Int32,
            self::Int64,
            self::Uint,
            self::Uint8,
            self::Uint16,
            self::Uint32,
            self::Uint64,
            self::Uintptr => true,
            default => false,
        };
    }

    public function isString(): bool
    {
        return $this === self::String;
    }
}
