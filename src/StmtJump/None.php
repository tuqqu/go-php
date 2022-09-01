<?php

declare(strict_types=1);

namespace GoPhp\StmtJump;

class None implements StmtJump
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function get(): self
    {
        return self::$instance ??= new self();
    }
}
