<?php

declare(strict_types=1);

namespace GoPhp\Tests\Unit\Env;

use GoPhp\Env\Environment;
use GoPhp\Env\Error\CannotBeMutatedError;
use GoPhp\Env\Error\UndefinedValueError;
use GoPhp\GoValue\Int\IntValue;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    public function testWithoutEnclosing(): void
    {
        $env = new Environment();

        $valueA = new IntValue(1);
        $valueB = new IntValue(2);

        $env->defineVar('a', $valueA, $valueA->type());
        $env->defineConst('b', $valueB, $valueB->type());

        self::assertSame($valueA, $env->get('a')->unwrap());
        self::assertSame($valueB, $env->get('b')->unwrap());
        self::assertSame($valueA, $env->getMut('a')->unwrap());

        $this->expectException(CannotBeMutatedError::class);
        $env->getMut('b');

        $this->expectException(UndefinedValueError::class);
        $env->get('c');
    }

    public function testWithEnclosing(): void
    {
        $enclosing = new Environment();
        $env = new Environment(enclosing: $enclosing);

        $valueA = new IntValue(1);
        $valueB = new IntValue(2);
        $valueC = new IntValue(3);
        $valueD = new IntValue(4);

        $enclosing->defineVar('a', $valueA, $valueA->type());
        $enclosing->defineConst('b', $valueB, $valueB->type());

        $env->defineConst('a', $valueC, $valueC->type());

        // enclosing value is overwritten
        self::assertSame($valueC, $env->get('a')->unwrap());

        // enclosing value is extracted
        self::assertSame($valueB, $env->get('b')->unwrap());

        // enclosing value is mutable, but env is not
        $this->expectException(CannotBeMutatedError::class);
        $env->getMut('a');

        $env->defineVar('b', $valueD, $valueD->type());

        // enclosing value is const, but env is mutable
        self::assertSame($valueD, $env->getMut('a'));
    }
}
