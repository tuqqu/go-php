<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit;

use GoPhp\Error\ProgramError;
use GoPhp\GoType\FuncType;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\VoidFuncTypeValidator;
use PHPUnit\Framework\TestCase;

final class VoidFunctionValidatorTest extends TestCase
{
    private VoidFuncTypeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new VoidFuncTypeValidator('main', 'main');
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

        $this->expectException(ProgramError::class);

        $this->validator->validate($type);
    }
}