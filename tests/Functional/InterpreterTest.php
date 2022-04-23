<?php

declare(strict_types=1);

namespace GoPhp\Tests\Functional;

use GoPhp\Interpreter;
use GoPhp\Stream\StringStreamProvider;
use PHPUnit\Framework\TestCase;

final class InterpreterTest extends TestCase
{
    private const SRC_FILES_PATH = __DIR__ . '/files/';
    private const OUTPUT_FILES_PATH = __DIR__ . '/output/';

    /**
     * @dataProvider sourceFileProvider
     */
    public function testSourceFiles(string $goProgram, string $expectedOutput): void
    {
        $stdout = '';
        $stderr = '';
        $stdin = '';

        $streams = new StringStreamProvider(
            $stdout,
            $stderr,
            $stdin,
        );

        $interpreter = Interpreter::fromString(
            src: $goProgram,
            streams: $streams,
        );

        $interpreter->run();

        self::assertSame($expectedOutput, $stderr);
    }

    public function sourceFileProvider(): iterable
    {
        $files = [
            'variable_decl',
            'const_decl',
            'iota',
            'if',
            'for',
            'for_range',
            'array', //fixme fmt
            'slice', //fixme fmt, copy, :1
            'type_conversion', //fixme fmt float
        ];

        foreach ($files as $file) {
            $goProgram = \file_get_contents(\sprintf('%s%s.go', self::SRC_FILES_PATH, $file));
            $expectedOutput = \file_get_contents(\sprintf('%s%s', self::OUTPUT_FILES_PATH, $file));

            yield $file => [$goProgram, $expectedOutput];
        }
    }
}
