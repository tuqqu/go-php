<?php

declare(strict_types=1);

namespace GoPhp\Env;

use GoPhp\Error\InternalError;
use GoPhp\Error\RuntimeError;
use GoPhp\GoType\GoType;
use GoPhp\GoType\PointerType;
use GoPhp\GoValue\Func\FuncValue;
use GoPhp\GoValue\Hashable;

final class MethodSet
{
    /**
     * @var array<string, array<string, FuncValue>>
     */
    private array $methods = [];

    public function tryGet(GoType&Hashable $type, string $name): ?FuncValue
    {
        $type = self::normalizeType($type);

        return $this->methods[$type->hash()][$name] ?? null;
    }

    public function add(GoType $type, string $name, FuncValue $method): void
    {
        $type = self::normalizeType($type);

        if ($this->has($type, $name)) {
            throw RuntimeError::redeclaredNameInBlock($name, $type->name());
        }

        $hash = $type->hash();
        $this->methods[$hash] ??= [];
        $this->methods[$hash][$name] = $method;
    }

    public function has(GoType&Hashable $type, string $name): bool
    {
        return isset($this->methods[$type->hash()][$name]);
    }

    private static function normalizeType(GoType $type): GoType&Hashable
    {
        $normalizedType = $type instanceof PointerType
            ? $type->pointsTo
            : $type;

        if (!$normalizedType instanceof Hashable) {
            throw InternalError::unreachable($normalizedType);
        }

        return $normalizedType;
    }
}
