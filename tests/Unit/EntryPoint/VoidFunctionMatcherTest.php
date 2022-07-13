<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\EntryPoint;

use GoPhp\EntryPoint\VoidFunctionMatcher;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\Func\Signature;
use PHPUnit\Framework\TestCase;

final class VoidFunctionMatcherTest extends TestCase
{
    private VoidFunctionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new VoidFunctionMatcher('main', 'main');
    }

    public function testSuccessValidate(): void
    {
        $signature = new Signature(
            new Params([]),
            new Params([]),
        );

        self::assertTrue($this->matcher->matches('main', 'main', $signature));
    }

    public function testWrongSignature(): void
    {
        $signature = new Signature(
            new Params([new Param(NamedType::Int)]),
            new Params([new Param(NamedType::Int)]),
        );

        self::assertFalse($this->matcher->matches('main', 'main', $signature));
    }

    public function testWrongFunctionName(): void
    {
        $signature = new Signature(
            new Params([]),
            new Params([]),
        );

        self::assertFalse($this->matcher->matches('main', 'print', $signature));
    }
}
