<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Sequence
{
    public function get(int $at): GoValue;

    public function set(GoValue $value, int $at): void;

    public function len(): int;
}
