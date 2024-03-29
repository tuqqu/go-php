<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\Environment;
use GoPhp\Error\RuntimeError;
use GoPhp\GoValue\Int\IntValue;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    public function testWithoutEnclosing(): void
    {
        $env = Environment::new();

        $valueA = new IntValue(1);
        $valueB = new IntValue(2);

        $env->defineVar('a', $valueA, $valueA->type());
        $env->defineConst('b', $valueB, $valueB->type());

        self::assertSame($valueA, $env->get('a', 'main')->unwrap());
        self::assertSame($valueB, $env->get('b', 'main')->unwrap());

        $this->expectException(RuntimeError::class);
        $env->get('c', 'main');
    }

    public function testWithEnclosing(): void
    {
        $enclosing = Environment::new();
        $env = Environment::fromEnclosing($enclosing);

        $valueA = new IntValue(1);
        $valueB = new IntValue(2);
        $valueC = new IntValue(3);
        $valueD = new IntValue(4);

        $enclosing->defineVar('a', $valueA, $valueA->type());
        $enclosing->defineConst('b', $valueB, $valueB->type());

        $env->defineConst('a', $valueC, $valueC->type());

        // enclosing value is overwritten
        self::assertSame($valueC, $env->get('a', 'main')->unwrap());

        // enclosing value is extracted
        self::assertSame($valueB, $env->get('b', 'main')->unwrap());

        $env->defineVar('b', $valueD, $valueD->type());

        self::assertSame($valueD, $env->get('b', 'main')->unwrap());
    }
}
