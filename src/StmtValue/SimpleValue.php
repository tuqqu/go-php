<?php

declare(strict_types=1);

namespace GoPhp\StmtValue;

enum SimpleValue implements StmtValue
{
    case None;
    case Break;
    case Continue;
}
