<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that is stored and can be addressed either by name or by reference.
 *
 * @template T
 * @template-extends GoValue<T>
 */
interface AddressableValue extends GoValue
{
    /**
     * Constant name of the value.
     */
    public const string NAME = 'value';

    /**
     * Must be called wherever the value becomes stored (e.g. in a variable).
     */
    public function makeAddressable(): void;

    /**
     * Whether the value is stored somewhere.
     */
    public function isAddressable(): bool;

    /**
     * Returns the name with which current value has been addressed.
     * Can dynamically change depending on the name it was addressed with.
     */
    public function getName(): string;

    /**
     * Returns the fully qualified name.
     */
    public function getQualifiedName(): string;

    /**
     * Sets the name with which current value has been addressed.
     */
    public function addressedWithName(string $name, ?string $namespace = null): void;

    /**
     * Copying an addressable value results in an addressable value.
     */
    public function copy(): self;
}
