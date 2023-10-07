<?php

declare(strict_types=1);

namespace GoPhp;

use GoPhp\Error\PanicError;

final class PanicPointer
{
    public ?PanicError $panic = null;
}
