<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

use GoPhp\Stream\StreamProvider;

interface Invocable
{
    public function __invoke(StreamProvider $streams, GoValue ...$argv): GoValue;
}
