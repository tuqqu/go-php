<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Sequence
{
    public function get(GoValue $at): GoValue;

    public function set(GoValue $value, GoValue $at): void;

    public function len(): int;

    public function iter(): iterable;
}
