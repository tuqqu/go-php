<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that supports invoking with a () operator.
 *
 * @template T of GoValue
 */
interface Invocable
{
    /**
     * @param T ...$argv
     */
    public function __invoke(GoValue ...$argv): GoValue;
}
