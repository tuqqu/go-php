<?php

declare(strict_types=1);

namespace GoPhp;

class EnvVarSet
{
    private const DEFAULT_GOROOT = '/usr/local/go';
    private const DEFAULT_GOPATH = '~/go';

    public function __construct(
        public readonly string $goroot = self::DEFAULT_GOROOT,
        public readonly string $gopath = self::DEFAULT_GOPATH,
    ) {}
}
