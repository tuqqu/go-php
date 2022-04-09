<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\EntryPoint;

use GoPhp\EntryPoint\MainEntryPoint;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\Func\Signature;
use PHPUnit\Framework\TestCase;

final class MainEntryPointTest extends TestCase
{
    public function testSuccessValidate(): void
    {
        $validator = new MainEntryPoint();
        $signature = new Signature(
            new Params([]),
            new Params([]),
        );

        self::assertTrue($validator->validate('main', 'main', $signature));
    }

    public function testWrongSignature(): void
    {
        $validator = new MainEntryPoint();
        $signature = new Signature(
            new Params([new Param(NamedType::Int)]),
            new Params([new Param(NamedType::Int)]),
        );

        self::assertFalse($validator->validate('main', 'main', $signature));
    }

    public function testWrongPackageName(): void
    {
        $validator = new MainEntryPoint();
        $signature = new Signature(
            new Params([]),
            new Params([]),
        );

        self::assertFalse($validator->validate('fmt', 'main', $signature));
    }
}
