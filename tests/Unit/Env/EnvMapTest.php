<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\EnvMap;
use GoPhp\Env\EnvValue;
use GoPhp\Error\ProgramError;
use GoPhp\GoType\NamedType;
use PHPUnit\Framework\TestCase;

final class EnvMapTest extends TestCase
{
    private EnvMap $map;
    private EnvValue $valueA;
    private EnvValue $valueB;
    private EnvValue $valueGlobal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->map = new EnvMap();
        $this->valueA = self::createEnvValue('a');
        $this->valueB = self::createEnvValue('b');
        $this->valueGlobal = self::createEnvValue('g');

        $this->map->add($this->valueA, 'A');
        $this->map->add($this->valueB, 'B');
        $this->map->add($this->valueGlobal, '');
    }

    public function testGet(): void
    {
        self::assertSame($this->valueA, $this->map->get('a', 'A', true));
        self::assertSame($this->valueB, $this->map->get('b', 'B', true));
        self::assertSame($this->valueGlobal, $this->map->get('g', '', true));

        $this->expectException(ProgramError::class);
        $this->map->get('b', 'A', true);

        $this->expectException(ProgramError::class);
        $this->map->get('b', '', true);

        $this->expectException(ProgramError::class);
        $this->map->get('c', 'A', true);

        $this->expectException(ProgramError::class);
        $this->map->get('c', '', true);
    }

    public function testTryGet(): void
    {
        self::assertNull($this->map->tryGet('c', 'A', true));
        self::assertNull($this->map->tryGet('c', '', true));

        self::assertSame($this->valueA, $this->map->tryGet('a', 'A', true));
    }

    public function testAdd(): void
    {
        $this->map->add(self::createEnvValue('a'), 'B');
        $this->map->add(self::createEnvValue('a'), '');

        $this->expectException(ProgramError::class);
        $this->map->add(self::createEnvValue('a'), 'A');
    }

    private static function createEnvValue(string $name): EnvValue
    {
        return new EnvValue($name, NamedType::Int->defaultValue());
    }
}
