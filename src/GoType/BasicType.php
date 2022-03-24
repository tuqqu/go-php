<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\FloatValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\IntValue;
use GoPhp\GoValue\StringValue;

enum BasicType: string implements ValueType
{
    case UntypedInt = 'untyped_int';
    case UntypedFloat = 'untyped_float';

    // signed ints
    case Int = 'int';
    case Int8 = 'int8';
    case Int16 = 'int16';
    case Int32 = 'int32'; // Rune
    case Int64 = 'int64';

    // unsigned ints
    case Uint = 'uint';
    case Uint8 = 'uint8'; // Byte
    case Uint16 = 'uint16';
    case Uint32 = 'uint32';
    case Uint64 = 'uint64';
    case Uintptr = 'uintptr';

    // floats
    case Float32 = 'float32';
    case Float64 = 'float64';

    // complex nums
    case Complex64 = 'complex64';
    case Complex128 = 'complex128';

    case Bool = 'bool';
    case String = 'string';
//
//    // non-ref
//    case Array = 'array';
//    case Struct = 'struct';
//
//    // ref
//    case Pointer = 'pointer';
//    case Slice = 'slice';
//    case Map = 'map';
//    case Func = 'func';
//    case Channel = 'channel';
//    case Interface = 'interface';

    public function name(): string
    {
        return $this->value;
    }

    public function equals(ValueType $type): bool
    {
        return $this === $type;
    }

    public function reify(): static
    {
        return match ($this) {
            self::UntypedInt => self::Int,
            self::UntypedFloat => self::Float32,
            default => $this,
        };
    }

    public function defaultValue(): GoValue
    {
        return match ($this) {
            self::Int => new IntValue(0, self::Int),
            self::Int8 => new IntValue(0, self::Int8),
            self::Int16 => new IntValue(0, self::Int16),
            self::Int32 => new IntValue(0, self::Int32),
            self::Int64 => new IntValue(0, self::Int64),
            self::Uint => new IntValue(0, self::Uint),
            self::Uint8 => new IntValue(0, self::Uint8),
            self::Uint16 => new IntValue(0, self::Uint16),
            self::Uint32 => new IntValue(0, self::Uint32),
            self::Uint64 => new IntValue(0, self::Uint64),
            self::Uintptr => new IntValue(0, self::Uintptr),
            self::Float32 => new FloatValue(0, self::Float32),
            self::Float64 => new FloatValue(0, self::Float64),
//            self::Complex64 =>
//            self::Complex128 =>
            self::Bool => BoolValue::False,
            self::String => new StringValue(''),
            // fixme remvoe after complex
            default => throw new \UnhandledMatchError('not impls def val'),
        };
    }

    public function conforms(ValueType $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return match ($this) {
            self::UntypedInt => match ($other) {
                self::Int,
                self::Int8,
                self::Int32,
                self::Int64,
                self::Uint,
                self::Uint8,
                self::Uint16,
                self::Uint32,
                self::Uint64,
                self::Uintptr, => true,
                default => false,
            },
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
                self::UntypedInt => true,
                default => $this->equals($other),
            },
            self::UntypedFloat => match ($other) {
                self::Float32, self::Float64 => true,
                default => $this->equals($other),
            },
            self::Float64, self::Float32 => match ($other) {
                self::UntypedFloat => true,
                default => $this->equals($other),
            },
            default => $this->equals($other),
        };
    }
}
