<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\GoValue;

use GoPhp\Error\OperationError;
use GoPhp\Error\TypeError;
use GoPhp\GoValue\BoolValue;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\GoValue\StringValue;
use GoPhp\Operator;
use PHPUnit\Framework\TestCase;

final class BoolValueTest extends TestCase
{
    public function testToString(): void
    {
        self::assertSame('false', BoolValue::False->toString());
        self::assertSame('true', BoolValue::True->toString());
    }

    public function testFromBool(): void
    {
        self::assertSame(BoolValue::True, BoolValue::fromBool(true));
        self::assertSame(BoolValue::False, BoolValue::fromBool(false));
    }

    public function testOperations(): void
    {
        self::assertSame(BoolValue::False, BoolValue::True->operate(Operator::LogicNot));
        self::assertSame(BoolValue::True, BoolValue::False->operate(Operator::LogicNot));

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::LogicAnd, BoolValue::True));
        self::assertSame(BoolValue::False, BoolValue::True->operateOn(Operator::LogicAnd, BoolValue::False));

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::LogicOr, BoolValue::True));
        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::LogicOr, BoolValue::False));

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::EqEq, BoolValue::True));
        self::assertSame(BoolValue::True, BoolValue::False->operateOn(Operator::NotEq, BoolValue::True));
    }

    public function testInvalidOperation(): void
    {
        $this->expectException(OperationError::class);

        self::assertSame(BoolValue::True, BoolValue::True->operate(Operator::Plus));

        $this->expectException(OperationError::class);

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::Minus, BoolValue::True));
    }

    public function testIncompatibleValues(): void
    {
        $this->expectException(TypeError::class);

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::LogicAnd, new UintValue(1)));

        $this->expectException(TypeError::class);

        self::assertSame(BoolValue::True, BoolValue::True->operateOn(Operator::LogicOr, new StringValue("str")));
    }
}
