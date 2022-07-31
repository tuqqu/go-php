<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

final class Params
{
    /** @var Param[] */
    public readonly array $params;
    public readonly int $len;
    public readonly bool $variadic;

    /**
     * @param Param[] $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->len = \count($params);
        $this->variadic = empty($params) ? false : $params[$this->len - 1]->variadic;
    }

    public static function empty(): self
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

    public function void(): bool
    {
        return $this->len === 0;
    }

    /**
     * @return Param[]
     * @psalm-return iterable<Param>
     */
    public function iter(): iterable
    {
        yield from $this->params;
    }
}
