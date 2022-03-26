<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\GoValue\GoValue;

function assert_type_conforms(GoValue $a, GoValue $b): void
{
    if (!$a->type()->conforms($b->type())) {
        throw new \Exception('type error');
    }
}
