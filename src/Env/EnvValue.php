<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Env\ValueConverter\AddressableValueConverter;
use GoPhp\Env\ValueConverter\TypeableValueConverter;
use GoPhp\Env\ValueConverter\ValueConverter;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\GoValue;
use GoPhp\Operator;

use function GoPhp\assert_types_compatible;

final class EnvValue
{
    public readonly string $name;
    private readonly GoValue $value;

    public function __construct(string $name, GoValue $value, ?GoType $type = null)
    {
        /** @var iterable<ValueConverter> $valueConverters */
        static $valueConverters = [
            new TypeableValueConverter(),
            new AddressableValueConverter(),
        ];

        $this->name = $name;

        if ($type !== null) {
            foreach ($valueConverters as $converter) {
                if ($converter->supports($value, $type)) {
                    $value = $converter->convert($value, $type);
                    break;
                }
            }

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
}
