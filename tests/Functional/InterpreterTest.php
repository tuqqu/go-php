<?php

declare(strict_types=1);

namespace GoPhp\Tests\Functional;

use GoPhp\Interpreter;
use GoPhp\Stream\StringStreamProvider;
use PHPUnit\Framework\TestCase;

final class InterpreterTest extends TestCase
{
    private const SRC_FILES_PATH = __DIR__ . '/files';
    private const OUTPUT_FILES_PATH = __DIR__ . '/output';

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

        $interpreter = new Interpreter(
            source: $goProgram,
            streams: $streams,
            gopath: __DIR__ . '/imports/',
        );

        $interpreter->run();

        self::assertSame($expectedOutput, $stderr);
    }

    public function sourceFileProvider(): iterable
    {
//        'array', //fixme fmt
//        'slice', //fixme fmt, copy, :1
//        'type_conversion', //fixme fmt float
//        'slicing', //fixme fmt float

        $files = \glob(\sprintf('%s/*.go', self::SRC_FILES_PATH));

        foreach ($files as $file) {
            $goProgram = \file_get_contents($file);
            $expectedOutput = \file_get_contents(
                \sprintf('%s/%s.out', self::OUTPUT_FILES_PATH, \basename($file, '.go'))
            );

            yield $file => [$goProgram, $expectedOutput];
        }
    }
}
