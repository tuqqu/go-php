<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\PointerType;
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
        $type = self::normalizeType($type);

        return $this->methods[$type][$name] ?? null;
    }

    public function add(GoType $type, string $name, FuncValue $method): void
    {
        $type = self::normalizeType($type);

        if ($this->has($type, $name)) {
            throw RuntimeError::redeclaredNameInBlock($name, $type->name());
        }

        $this->methods[$type] ??= new \ArrayObject();
        $this->methods[$type][$name] = $method;
    }

    private function has(GoType $type, string $name): bool
    {
        return isset($this->methods[$type][$name]);
    }

    private static function normalizeType(GoType $type): GoType
    {
        return $type instanceof PointerType
            ? $type->pointsTo
            : $type;
    }
}
