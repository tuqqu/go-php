<?php

declare(strict_types=1);

namespace GoPhp\GoValue\Array;

final class UnderlyingArray
{
    public function __construct(public array $array = []) {}
}
