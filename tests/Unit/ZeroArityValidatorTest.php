<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit;

use GoPhp\Error\RuntimeError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\ZeroArityValidator;
use PHPUnit\Framework\TestCase;

final class ZeroArityValidatorTest extends TestCase
{
    private ZeroArityValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ZeroArityValidator('main', 'main');
    }

    public function testValidate(): void
    {
        $type = new FuncType(
            Params::fromEmpty(),
            Params::fromEmpty(),
        );

        $this->validator->validate($type);

        self::assertTrue(true);
    }

    public function testFailedValidate(): void
    {
        $type = new FuncType(
            Params::fromParam(new Param(NamedType::Int)),
            Params::fromParam(new Param(NamedType::Int)),
        );

        $this->expectException(RuntimeError::class);

        $this->validator->validate($type);
    }
}
