<?php

declare(strict_types=1);

namespace GoPhp;

enum ExitCode: int
{
    case Success = 0;
    case Failure = 1;
}
