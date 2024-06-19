<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Env\EnvMap;

use function GoPhp\construct_qualified_name;

/**
 * Default implementation of AddressableValue interface.
 */
trait AddressableTrait
{
    private const string NULL_NAMESPACE = EnvMap::NAMESPACE_TOP;

    protected bool $addressable = false;
    protected ?string $name = null;
    protected string $namespace = self::NULL_NAMESPACE;

    public function makeAddressable(): void
    {
        $this->addressable = true;
    }

    public function isAddressable(): bool
    {
        return $this->addressable;
    }

    public function addressedWithName(string $name, ?string $namespace = null): void
    {
        $this->name = $name;
        $this->namespace = $namespace ?? self::NULL_NAMESPACE;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function getQualifiedName(): string
    {
        return construct_qualified_name($this->getName(), $this->namespace);
    }
}
