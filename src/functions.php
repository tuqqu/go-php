<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoType\ValueType;
use GoPhp\GoValue\GoValue;

function assert_values_compatible(GoValue $a, GoValue $b): void
{
    assert_types_compatible($a->type(), $b->type());
}

function assert_types_compatible(ValueType $a, ValueType $b): void
{
    if (!$a->isCompatible($b)) {
        throw TypeError::incompatibleTypes($a, $b);
    }
}

function assert_argc(array $actualArgv, int $expectedArgc, bool $variadic = false): void
{
    $actualArgc = \count($actualArgv);
    if ($actualArgc < $expectedArgc || (!$variadic && $actualArgc > $expectedArgc)) {
        throw OperationError::wrongArgumentNumber($expectedArgc, $actualArgc);
    }
}
