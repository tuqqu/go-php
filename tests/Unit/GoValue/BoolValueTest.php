<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\GoValue;

use GoPhp\Error\RuntimeError;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\GoValue\String\StringValue;
use GoPhp\Operator;
use PHPUnit\Framework\TestCase;

final class BoolValueTest extends TestCase
{
    public function testToString(): void
    {
        self::assertSame('false', BoolValue::false()->toString());
        self::assertSame('true', BoolValue::true()->toString());
    }

    public function testOperations(): void
    {
        self::assertFalse(BoolValue::true()->operate(Operator::LogicNot)->unwrap());
        self::assertTrue(BoolValue::false()->operate(Operator::LogicNot)->unwrap());

        self::assertTrue(BoolValue::true()->operateOn(Operator::LogicAnd, BoolValue::true())->unwrap());
        self::assertFalse(BoolValue::true()->operateOn(Operator::LogicAnd, BoolValue::false())->unwrap());

        self::assertTrue(BoolValue::true()->operateOn(Operator::LogicOr, BoolValue::true())->unwrap());
        self::assertTrue(BoolValue::true()->operateOn(Operator::LogicOr, BoolValue::false())->unwrap());

        self::assertTrue(BoolValue::true()->operateOn(Operator::EqEq, BoolValue::true())->unwrap());
        self::assertTrue(BoolValue::false()->operateOn(Operator::NotEq, BoolValue::true())->unwrap());
    }

    public function testInvalidOperation(): void
    {
        $this->expectException(RuntimeError::class);

        BoolValue::true()->operate(Operator::Plus);

        $this->expectException(RuntimeError::class);

        BoolValue::true()->operateOn(Operator::Minus, BoolValue::true());
    }

    public function testIncompatibleValues(): void
    {
        $this->expectException(RuntimeError::class);

        BoolValue::true()->operateOn(Operator::LogicAnd, new UintValue(1));

        $this->expectException(RuntimeError::class);

        BoolValue::true()->operateOn(Operator::LogicOr, new StringValue("str"));
    }
}
