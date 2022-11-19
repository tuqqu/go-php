<?php

declare(strict_types=1);

namespace GoPhp;

use GoParser\Ast\Expr\Expr;
use GoParser\Ast\KeyedElement;
use GoPhp\GoValue\GoValue;

/**
 * @psalm-type Evaluator = callable(Expr): GoValue
 */
interface CompositeValueBuilder
{
    /**
     * @param Evaluator $evaluator
     */
    public function push(KeyedElement $element, callable $evaluator): void;

    public function build(): GoValue;
}
