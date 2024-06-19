<?php

declare(strict_types=1);

namespace GoPhp\Tests\Functional;

use GoPhp\EnvVarSet;
use GoPhp\Interpreter;
use GoPhp\Stream\StringStreamProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function glob;
use function file_get_contents;
use function sprintf;
use function basename;

final class InterpreterTest extends TestCase
{
    private const string SRC_FILES_PATH = __DIR__ . '/files';
    private const string OUTPUT_FILES_PATH = __DIR__ . '/output';

    #[DataProvider('sourceFileProvider')]
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

        $interpreter = Interpreter::create(
            source: $goProgram,
            streams: $streams,
            envVars: new EnvVarSet(
                goroot: __DIR__ . '/imports',
            ),
        );

        $interpreter->run();

        self::assertSame($expectedOutput, $stderr);
    }

    public static function sourceFileProvider(): iterable
    {
        $files = glob(sprintf('%s/*.go', self::SRC_FILES_PATH));

        foreach ($files as $file) {
            $goProgram = file_get_contents($file);
            $expectedOutput = file_get_contents(
                sprintf('%s/%s.out', self::OUTPUT_FILES_PATH, basename($file, '.go')),
            );

            yield $file => [$goProgram, $expectedOutput];
        }
    }
}
