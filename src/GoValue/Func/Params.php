<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

final class Params
{
    /** @var Param[] */
    public readonly array $params;
    public readonly int $len;

    /**
     * @param Param[] $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->len = \count($params);
    }

    public function __toString(): string
    {
        $types = [];
        foreach ($this->params as $param) {
            $types[] = $param->type->name();
        }

        return \implode(', ', $types);
    }

    public function void(): bool
    {
        return $this->len === 0;
    }

    /**
     * @return Param[]
     */
    public function iter(): iterable
    {
        yield from $this->params;
    }
}
