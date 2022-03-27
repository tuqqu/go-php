<?php

declare(strict_types=1);

namespace GoPhp\GoType;

/**
 * Not a real Go type, but an internal marker
 * of a set of types returned from a function call.
 */
final class TupleType implements ValueType
{
    public readonly array $types;
    public readonly string $name;

    /**
     * @param ValueType[] $types
     */
    public function __construct(
        array $types,
    ) {
        if (empty($types)) {
            throw new \Exception('cannot be empty');//fixme
        }

        $this->types = $types;
        $this->name = \sprintf(
            '(%s)',
            self::typesToString($types),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function reify(): static
    {
        throw new \Exception();
    }

    public function defaultValue(): never
    {
        throw new \Exception('cannot have def value');
    }

    public function equals(ValueType $other): bool
    {
        throw new \Exception();
    }

    public function conforms(ValueType ...$other): bool
    {
        foreach ($this->types as $type) {
            if (!$type->conforms($type)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ValueType[] $types
     */
    private static function typesToString(array $types): string
    {
        $str = '';
        foreach ($types as $type) {
            $str .= $type->name();
            $str .= ','; //fixme comma
        }

        return $str;
    }
}
