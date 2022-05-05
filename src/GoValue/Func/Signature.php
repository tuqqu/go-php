<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoType\FuncType;

final class Signature
{
    public readonly FuncType $type;
    public readonly int $arity;
    public readonly int $returnArity;
    public readonly bool $variadic;

    public function __construct(
        public readonly Params $params,
        public readonly Params $returns,
    ) {
        $this->type = new FuncType($params, $returns);
        $this->arity = $this->params->len;
        $this->returnArity = $this->returns->len;
        $this->variadic = $this->params->variadic;
    }
}
