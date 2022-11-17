<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Env\EnvMap;

final class ScopeResolver
{
    private const NO_PACKAGE = EnvMap::NAMESPACE_TOP;

    public string $currentPackage = self::NO_PACKAGE;
    private bool $packageScope = false;

    public function enterPackageScope(): void
    {
        $this->packageScope = true;
    }

    public function exitPackageScope(): void
    {
        $this->packageScope = false;
    }

    public function resolveDefinitionScope(): string
    {
        return $this->packageScope ? $this->currentPackage : self::NO_PACKAGE;
    }

    public function getCurrentPackage(): string
    {
        return $this->currentPackage;
    }

    public function setCurrentPackage(string $currentPackage): void
    {
        $this->currentPackage = $currentPackage;
    }
}
