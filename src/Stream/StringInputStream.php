<?php

declare(strict_types=1);

namespace GoPhp\Stream;

use GoPhp\Error\InternalError;

class StringInputStream implements InputStream
{
    /** @var resource */
    private mixed $handle;
    private readonly ResourceInputStream $resourceStream;

    public function __construct(string $input)
    {
        $handle = \fopen('php://memory', 'rb+');

        if ($handle === false) {
            throw new InternalError('cannot open in memory stream');
        }

        $this->handle = $handle;

        \fwrite($this->handle, $input);
        \rewind($this->handle);

        $this->resourceStream = new ResourceInputStream($this->handle);
    }

    public function __destruct()
    {
        \fclose($this->handle);
    }

    public function getChar(): ?string
    {
        return $this->resourceStream->getChar();
    }

    public function getLine(): ?string
    {
        return $this->resourceStream->getLine();
    }
}
