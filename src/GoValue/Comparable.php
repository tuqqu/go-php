<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Comparable extends GoValue
{
    public function greater(self $other): BoolValue;

    public function greaterEq(self $other): BoolValue;

    public function less(self $other): BoolValue;

    public function lessEq(self $other): BoolValue;
}
