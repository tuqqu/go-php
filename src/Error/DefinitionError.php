<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\ValueType;

final class DefinitionError extends \LogicException
{
    public static function indexOutOfRange(int $key, int $len): self
    {
        return new self(\sprintf('index out of range [%d] with length %d', $key, $len));
    }

    public static function uninitialisedConstant(string $name): self
    {
        return new self(\sprintf('Constant "%s" must have default value', $name));
    }

    public static function constantExpectsBasicType(ValueType $type): self
    {
        return new self(\sprintf('Constant must of basic type, got "%s"', $type->name()));
    }

    public static function uninitilisedVarWithNoType(): self
    {
        return new self('Variables must be typed or be initialised');
    }

    public static function assignmentMismatch(int $expected, int $actual): self
    {
        return new self('Assignment mismatch: %d variables, but got %d values', $expected, $actual);
    }
}
