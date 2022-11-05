<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\EnvValue;

use GoPhp\Env\EnvValue;
use GoPhp\GoType\NamedType;
use GoPhp\GoValue\Int\IntValue;
use GoPhp\GoValue\Int\Uint32Value;
use GoPhp\GoValue\Int\UintValue;
use GoPhp\GoValue\Int\UntypedIntValue;
use PHPUnit\Framework\TestCase;

final class EnvValueTest extends TestCase
{
    public function testCreation(): void
    {
        $valueA = new IntValue(1);
        $envValue = new EnvValue('a', $valueA);

        self::assertSame($valueA, $envValue->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(NamedType::Int, $envValue->unwrap()->type());
    }

    public function testCreationWithConversion(): void
    {
        // untyped value, untyped type
        $valueA = new UntypedIntValue(1);
        $envValue = new EnvValue('a', $valueA, NamedType::Uint);

        self::assertInstanceOf(UintValue::class, $envValue->unwrap());
        self::assertSame($valueA->unwrap(), $envValue->unwrap()->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(NamedType::Uint, $envValue->unwrap()->type());

        // untyped value, named type
        $valueB = new UntypedIntValue(1);
        $envValue = new EnvValue('a', $valueB, NamedType::Uint32);

        self::assertInstanceOf(Uint32Value::class, $envValue->unwrap());
        self::assertSame($valueB->unwrap(), $envValue->unwrap()->unwrap());
        self::assertSame('a', $envValue->name);
        self::assertEquals(NamedType::Uint32, $envValue->unwrap()->type());
    }
}
