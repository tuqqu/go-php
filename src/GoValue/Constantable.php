<?php

declare(strict_types=1);

namespace GoPhp\GoValue;

interface Constantable
{
    public function makeConst(): void;

    public function onMutate(): void;
}
