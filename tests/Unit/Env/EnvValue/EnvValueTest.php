<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env\EnvValue;

use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\EnvValue\MutableValue;
use GoPhp\Error\TypeError;
use GoPhp\GoType\BasicType;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Uint32Value;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use GoPhp\GoValue\StringValue;
use PHPUnit\Framework\TestCase;

final class EnvValueTest extends TestCase
{
    public function testCreation(): void
    {
        $valueA = new IntValue(1);
        $envValue = new ImmutableValue('a', $valueA->type(), $valueA);

        self::assertSame($valueA, $envValue->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(BasicType::Int, $envValue->type);
    }

    public function testCreationWithConversion(): void
    {
        // untyped value, untyped type
        $valueA = new UntypedIntValue(1);
        $envValue = new ImmutableValue('a', BasicType::Uint, $valueA);

        self::assertInstanceOf(UintValue::class, $envValue->unwrap());
        self::assertSame($valueA->unwrap(), $envValue->unwrap()->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(BasicType::Uint, $envValue->type);

        // untyped value, typed type
        $valueB = new UntypedIntValue(1);
        $envValue = new MutableValue('a', BasicType::Uint32, $valueA);

        self::assertInstanceOf(Uint32Value::class, $envValue->unwrap());
        self::assertSame($valueA->unwrap(), $envValue->unwrap()->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(BasicType::Uint32, $envValue->type);
    }

    public function testMutableSet(): void
    {
        $envValue = new MutableValue('a', BasicType::Uint32, new Uint32Value(1));
        $envValue->set(new UntypedIntValue(2));

        self::assertInstanceOf(Uint32Value::class, $envValue->unwrap());
        self::assertSame(2, $envValue->unwrap()->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(BasicType::Uint32, $envValue->type);
    }

    public function testMutableFailedSet(): void
    {
        $envValue = new MutableValue('a', BasicType::Uint32, new Uint32Value(1));

        $this->expectException(TypeError::class);
        $envValue->set(new StringValue("2"));
    }
}
