<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\EnvValue\EnvValue;
use GoPhp\Env\EnvValue\ImmutableValue;
use GoPhp\Env\Error\AlreadyDefinedError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\Env\ValueTable;
use GoPhp\Error\ProgramError;
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
        self::assertSame($this->valueA, $this->table->get('a', 'A', true));
        self::assertSame($this->valueB, $this->table->get('b', 'B', true));
        self::assertSame($this->valueGlobal, $this->table->get('g', '', true));

        $this->expectException(UndefinedValueError::class);
        $this->table->get('b', 'A', true);

        $this->expectException(UndefinedValueError::class);
        $this->table->get('b', '', true);

        $this->expectException(UndefinedValueError::class);
        $this->table->get('c', 'A', true);

        $this->expectException(UndefinedValueError::class);
        $this->table->get('c', '', true);
    }

    public function testTryGet(): void
    {
        self::assertNull($this->table->tryGet('c', 'A', true));
        self::assertNull($this->table->tryGet('c', '', true));

        self::assertSame($this->valueA, $this->table->tryGet('a', 'A', true));
    }

    public function testAdd(): void
    {
        $this->table->add(self::createEnvValue('a'), 'B');
        $this->table->add(self::createEnvValue('a'), '');

        $this->expectException(ProgramError::class);
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
