<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Func;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Invocable;
use GoPhp\Stream\StreamProvider;

interface Func extends Invocable
{
    public function __invoke(StreamProvider $streams, GoValue ...$argv): GoValue;
}
