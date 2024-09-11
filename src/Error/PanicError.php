<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoPhp\GoType\GoType;
use GoPhp\GoType\UntypedNilType;
use GoPhp\GoValue\AddressableValue;
use GoPhp\GoValue\Interface\InterfaceValue;
use GoPhp\GoValue\String\UntypedStringValue;
use RuntimeException;

use function sprintf;

class PanicError extends RuntimeException implements GoError
{
    public readonly AddressableValue $panicValue;

    public function __construct(AddressableValue $panicValue)
    {
        parent::__construct(sprintf('panic: %s', $panicValue->toString()));

        $this->panicValue = $panicValue;
    }

    public static function nilDereference(): self
    {
        return new self(new UntypedStringValue('runtime error: invalid memory address or nil pointer dereference'));
    }

    public static function nilMapAssignment(): self
    {
        return new self(new UntypedStringValue('assignment to entry in nil map'));
    }

    public static function interfaceConversion(InterfaceValue $interface, GoType $type): self
    {
        return new self(new UntypedStringValue(
            sprintf(
                'interface conversion: %s is %s, not %s',
                $interface->type()->name(),
                $interface->isNil()
                    ? UntypedNilType::NAME
                    : $interface->value->type()->name(),
                $type->name(),
            ),
        ));
    }

    public static function indexOutOfRange(int $index, int $len): self
    {
        return new self(new UntypedStringValue(
            sprintf('index out of range [%d] with length %d', $index, $len),
        ));
    }

    public function getPosition(): null
    {
        return null;
    }
}
