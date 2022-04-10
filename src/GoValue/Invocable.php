<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Invocable
{
    public function __invoke(GoValue ...$argv): GoValue;
}
