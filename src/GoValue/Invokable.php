<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Argv;

/**
 * Value that supports invoking with a () operator.
 */
interface Invokable
{
    public function __invoke(Argv $argv): GoValue;
}
