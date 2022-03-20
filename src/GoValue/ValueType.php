<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

enum ValueType: string
{
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

    // non-ref
    case Array = 'array';
    case Struct = 'struct';

    // ref
    case Pointer = 'pointer';
    case Slice = 'slice';
    case Map = 'map';
    case Func = 'func';
    case Channel = 'channel';
    case Interface = 'interface';
}
