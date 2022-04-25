<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Map;

use GoPhp\GoValue\GoValue;
use GoPhp\GoValue\Sequence;

interface Map extends Sequence
{
    public function has(GoValue $at): bool;

    public function delete(GoValue $at): void;
}
