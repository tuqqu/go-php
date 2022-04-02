<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\Error\AlreadyDefinedError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Env\ValueTable;
use GoPhp\GoType\BasicType;
use PHPUnit\Framework\TestCase;

final class ValueTableTest extends TestCase
{
    private ValueTable $table;
    private EnvValue $valueA;
    private EnvValue $valueB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = new ValueTable();
        $this->valueA = self::createEnvValue('a');
        $this->valueB = self::createEnvValue('b');

        $this->table->add($this->valueA);
        $this->table->add($this->valueB);
    }

    public function testHas(): void
    {
        self::assertTrue($this->table->has('a'));
        self::assertTrue($this->table->has('b'));

        self::assertFalse($this->table->has('c'));
    }

    public function testGet(): void
    {
        self::assertSame($this->valueA, $this->table->tryGet('a'));
        self::assertSame($this->valueB, $this->table->get('b'));

        self::assertNull($this->table->tryGet('c'));

        $this->expectException(UndefinedValueError::class);
        $this->table->get('c');
    }

    public function testAdd(): void
    {
        $this->expectException(AlreadyDefinedError::class);
        $this->table->add(self::createEnvValue('a'));
    }

    private static function createEnvValue(string $name): EnvValue
    {
        return new ImmutableValue(
            $name,
            BasicType::Int,
            BasicType::Int->defaultValue(),
        );
    }
}
