<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Default implementation of AddressableValue interface.
 */
trait AddressableTrait
{
    protected bool $addressable = false;
    protected ?string $name = null;

    public function makeAddressable(): void
    {
        $this->addressable = true;
    }

    public function isAddressable(): bool
    {
        return $this->addressable;
    }

    public function addressedWithName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }
}
