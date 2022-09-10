<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\Error\InternalError;

/**
 * @template-implements \ArrayAccess<int, Param>
 */
final class Params implements \ArrayAccess
{
    public readonly int $len;
    public readonly bool $variadic;
    public readonly bool $named;

    /** @var Param[] */
    private readonly array $params;

    /**
     * @param Param[] $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->len = \count($params);
        [$this->variadic, $this->named] = empty($params)
            ? [false, false]
            : [
                $params[$this->len - 1]->variadic,
                $params[$this->len - 1]->name !== null,
            ];
    }

    public static function fromEmpty(): self
    {
        return new self([]);
    }

    public static function fromParam(Param $param): self
    {
        return new self([$param]);
    }

    public function __toString(): string
    {
        $types = [];
        foreach ($this->params as $param) {
            $types[] = ($param->variadic ? '...' : '') . $param->type->name();
        }

        return \implode(', ', $types);
    }

    /**
     * @return Param[]
     * @psalm-return iterable<Param>
     */
    public function iter(): iterable
    {
        yield from $this->params;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->params[$offset]);
    }

    public function offsetGet(mixed $offset): Param
    {
        return $this->params[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw InternalError::unreachableMethodCall();
    }

    public function offsetUnset(mixed $offset): never
    {
        throw InternalError::unreachableMethodCall();
    }

}
