<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

/**
 * Value that supports invoking with a () operator.
 *
 * @template T of GoValue
 */
interface Invokable
{
    /**
     * @param T ...$argv
     */
    public function __invoke(GoValue ...$argv): GoValue;
}
