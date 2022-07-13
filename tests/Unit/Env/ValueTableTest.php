<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\Error\AlreadyDefinedError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Env\ValueTable;
use GoPhp\GoType\NamedType;
use PHPUnit\Framework\TestCase;

final class ValueTableTest extends TestCase
{
    private ValueTable $table;
    private EnvValue $valueA;
    private EnvValue $valueB;
    private EnvValue $valueGlobal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = new ValueTable();
        $this->valueA = self::createEnvValue('a');
        $this->valueB = self::createEnvValue('b');
        $this->valueGlobal = self::createEnvValue('g');

        $this->table->add($this->valueA, 'A');
        $this->table->add($this->valueB, 'B');
        $this->table->add($this->valueGlobal, '');
    }

    public function testGet(): void
    {
        self::assertSame($this->valueA, $this->table->get('a', 'A'));
        self::assertSame($this->valueB, $this->table->get('b', 'B'));
        self::assertSame($this->valueGlobal, $this->table->get('g', ''));

        $this->expectException(UndefinedValueError::class);
        $this->table->get('b', 'A');

        $this->expectException(UndefinedValueError::class);
        $this->table->get('b', '');

        $this->expectException(UndefinedValueError::class);
        $this->table->get('c', 'A');

        $this->expectException(UndefinedValueError::class);
        $this->table->get('c', '');
    }

    public function testTryGet(): void
    {
        self::assertNull($this->table->tryGet('c', 'A'));
        self::assertNull($this->table->tryGet('c', ''));

        self::assertSame($this->valueA, $this->table->tryGet('a', 'A'));
    }

    public function testAdd(): void
    {
        $this->table->add(self::createEnvValue('a'), 'B');
        $this->table->add(self::createEnvValue('a'), '');

        $this->expectException(AlreadyDefinedError::class);
        $this->table->add(self::createEnvValue('a'), 'A');
    }

    private static function createEnvValue(string $name): EnvValue
    {
        return new ImmutableValue(
            $name,
            NamedType::Int,
            NamedType::Int->defaultValue(),
        );
    }
}
