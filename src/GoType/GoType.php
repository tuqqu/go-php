<?php

declare(strict_types=1);

namespace GoPhp\GoType;

use GoPhp\GoValue\AddressableValue;

/**
 * Interface for all Go types.
 *
 * @see https://golang.org/ref/spec#Types
 */
interface GoType
{
    /**
     * Returns the name of the type.
     */
    public function name(): string;

    /**
     * Checks whether the type is equal to another type.
     */
    public function equals(self $other): bool;

    /**
     * Checks whether the type is compatible with another type.
     * Compatible types can be used in the same context.
     * For example, `int` and `uint` are compatible.
     * But `int` and `string` are not.
     */
    public function isCompatible(self $other): bool;

    /**
     * Returns the zero value of the type.
     * The zero value is the value that is assigned to a variable
     * when it is declared without an explicit initial value.
     * For example, the zero value of `int` is `0`.
     * The zero value of `string` is `""`.
     * The zero value of `[]int` is `nil`.
     * The zero value of `*int` is `nil`.
     * And so on.
     */
    public function zeroValue(): AddressableValue;

    /**
     * Converts a value to the type.
     * The value must be compatible with the type.
     */
    public function convert(AddressableValue $value): AddressableValue;
}
