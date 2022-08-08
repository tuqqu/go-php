<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that supports invoking with a () operator.
 */
interface Invocable
{
    public function __invoke(GoValue ...$argv): GoValue;
}
