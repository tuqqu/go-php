<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoType\FuncType;

final class Signature
{
    public readonly FuncType $type;
    public readonly int $arity;
    public readonly int $returnArity;

    public function __construct(
        public readonly Params $params,
        public readonly Params $returns,
    ) {
        $this->type = new FuncType($params, $returns);
        $this->arity = \count($this->params);
        $this->returnArity = \count($this->returns);
    }
}
