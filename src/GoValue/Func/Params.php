<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

final class Params
{
    /** @var Param[] */
    public readonly array $params;
    public readonly int $len;
    public readonly bool $variadic;
    public readonly bool $named;

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
     * fixme
     */
    public function iter(): iterable
    {
        yield from $this->params;
    }
}
