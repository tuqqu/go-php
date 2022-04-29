<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoValue\GoValue;

final class DefinitionError extends \LogicException
{
    public static function indexOutOfRange(int $index, int $len): self
    {
        return new self(\sprintf('index out of range [%d] with length %d', $index, $len));
    }

    public static function indexOfWrongType(GoValue $index, GoType|string $type, string $where): self
    {
        return new self(
            \sprintf(
                'cannot use "%s" (%s) as %s value in %s index',
                $index->toString(),
                $index->type()->name(),
                \is_string($type) ? $type::NAME : $type->name(),
                $where,
            )
        );
    }

    public static function uninitialisedConstant(string $name): self
    {
        return new self(\sprintf('Constant "%s" must have default value', $name));
    }

    public static function constantExpectsBasicType(GoType $type): self
    {
        return new self(\sprintf('Constant must of basic type, got "%s"', $type->name()));
    }

    public static function uninitilisedVarWithNoType(): self
    {
        return new self('Variables must be typed or be initialised');
    }

    public static function assignmentMismatch(int $expected, int $actual): self
    {
        return new self(\sprintf('Assignment mismatch: %d variables, but got %d values', $expected, $actual));
    }

    public static function labelAlreadyDefined(string $label): self
    {
        return new self(\sprintf('label %s already defined', $label));
    }

    public static function undefinedLabel(string $label): self
    {
        return new self(\sprintf('label %s not defined', $label));
    }
}
