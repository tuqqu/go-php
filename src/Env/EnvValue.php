<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\NamedType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoType\UntypedType;
use GoPhp\GoType\WrappedType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\SimpleNumber;
use GoPhp\GoValue\TypeValue;

use GoPhp\Operator;

use function GoPhp\assert_types_compatible;

final class EnvValue
{
    public readonly string $name;
    private readonly GoValue $value;

    public function __construct(string $name, GoValue $value, ?GoType $type = null)
    {
        $this->name = $name;

        if ($type !== null) {
            $value = self::convertIfNeeded($value, $type);

            if ($type instanceof UntypedNilType) {
                throw RuntimeError::untypedNilInVarDecl();
            }

            assert_types_compatible($type, $value->type());
        }

        $this->value = $value;
    }

    public function unwrap(): GoValue
    {
        if ($this->value instanceof AddressableValue) {
            $this->value->addressedWithName($this->name);
        }

        return $this->value;
    }

    public function copy(): self
    {
        return new self($this->name, $this->value->copy());
    }

    public function equals(self $other): bool
    {
        if ($this->name !== $other->name) {
            return false;
        }

        /** @var BoolValue $opResult */
        $opResult = $this->value->operateOn(Operator::EqEq, $other->value);

        return $opResult->unwrap();
    }

    private static function convertIfNeeded(GoValue $value, GoType $type): GoValue
    {
        if (
            $value instanceof SimpleNumber
            && $type instanceof NamedType
            && $value->type() instanceof UntypedType
        ) {
            return $value->becomeTyped($type);
        }

        if (
            $type instanceof WrappedType
            && !$value instanceof TypeValue
        ) {
            return $type->convert($value);
        }

        return $value;
    }
}
