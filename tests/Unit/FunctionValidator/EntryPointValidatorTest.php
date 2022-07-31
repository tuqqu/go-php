<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\FunctionValidator;

use GoPhp\Error\InternalError;
use GoPhp\FunctionValidator\VoidFunctionValidator;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Func\Param;
use GoPhp\GoValue\Func\Params;
use GoPhp\GoValue\Func\Signature;
use PHPUnit\Framework\TestCase;

final class VoidFunctionValidatorTest extends TestCase
{
    private VoidFunctionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new VoidFunctionValidator('main', 'main');
    }

    public function testValidate(): void
    {
        $signature = new Signature(
            Params::empty(),
            Params::empty(),
        );

        $this->validator->validate($signature);
        self::assertTrue(true);
    }

    public function testFailedValidate(): void
    {
        $signature = new Signature(
            Params::fromParam(new Param(NamedType::Int)),
            Params::fromParam(new Param(NamedType::Int)),
        );

        $this->expectException(InternalError::class);

        $this->validator->validate($signature);
    }
}
