<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\ProgramError;
use GoPhp\GoType\GoType;
use GoPhp\GoValue\Func\FuncValue;

final class MethodSet
{
    private \SplObjectStorage $methods;

    public function __construct()
    {
        $this->methods = new \SplObjectStorage();
    }

    public function tryGet(GoType $type, string $name): ?FuncValue
    {
        return $this->methods[$type][$name] ?? null;
    }

    public function get(GoType $type, string $name): FuncValue
    {
        return $this->tryGet($type, $name)
            ?? throw ProgramError::undefinedName($type->name(), $name);
    }

    public function add(GoType $type, string $name, FuncValue $method): void
    {
        if ($this->has($type, $name)) {
            throw ProgramError::redeclaredNameInBlock($type->name(), $name);
        }

        $this->methods[$type] ??= new \ArrayObject();
        $this->methods[$type][$name] = $method;
    }

    private function has(GoType $type, string $name): bool
    {
        return isset($this->methods[$type][$name]);
    }
}
