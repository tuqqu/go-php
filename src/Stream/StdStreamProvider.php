<?php

declare(strict_types=1);

namespace GoPhp\Stream;

final class StdStreamProvider implements StreamProvider
{
    private readonly OutputStream $stdout;
    private readonly OutputStream $stderr;
    private readonly InputStream $stdin;

    public function __construct()
    {
        $this->stdout = self::createOutputStream(\STDOUT);
        $this->stderr = self::createOutputStream(\STDERR);
        $this->stdin = self::createInputStream(\STDIN);
    }

    public function stdout(): OutputStream
    {
        return $this->stdout;
    }

    public function stderr(): OutputStream
    {
        return $this->stderr;
    }

    public function stdin(): InputStream
    {
        return $this->stdin;
    }

    private static function createOutputStream(mixed $resource): OutputStream
    {
        return new class ($resource) implements OutputStream {
            public function __construct(private mixed $resource) {}

            public function __destruct()
            {
                \fclose($this->resource);
            }

            public function write(string $str): void
            {
                \fwrite($this->resource, $str);
            }

            public function writeln(string $str): void
            {
                $this->write($str . "\n");
            }
        };
    }

    private static function createInputStream(mixed $resource): InputStream
    {
        return new class ($resource) implements InputStream {
            public function __construct(private mixed $resource) {}

            public function __destruct()
            {
                \fclose($this->resource);
            }

            public function getChar(): string
            {
                return \fgetc($this->resource);
            }

            public function getLine(): string
            {
                return \fgets($this->resource);
            }
        };
    }
}
