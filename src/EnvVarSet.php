<?php

declare(strict_types=1);

namespace GoPhp;

class EnvVarSet
{
    public const string DEFAULT_GOROOT = '/usr/local/go';
    public const string DEFAULT_GOPATH = '~/go';

    public function __construct(
        public readonly string $goroot = self::DEFAULT_GOROOT,
        public readonly string $gopath = self::DEFAULT_GOPATH,
    ) {}
}
