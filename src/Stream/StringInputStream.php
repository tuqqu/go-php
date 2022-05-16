<?php

declare(strict_types=1);

namespace GoPhp\Stream;

class StringInputStream implements InputStream
{
    private readonly mixed $handle;
    private readonly ResourceInputStream $resourceStream;

    public function __construct(string $input)
    {
        $this->handle = \fopen('php://memory', 'rb+');
        \fwrite($this->handle, $input);
        \rewind($this->handle);

        $this->resourceStream = new ResourceInputStream($this->handle);
    }

    /**
     * @psalm-suppress InaccessibleProperty
     */
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
