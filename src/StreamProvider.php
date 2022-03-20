<?php

declare(strict_types=1);

namespace GoPhp;

interface StreamProvider
{
    /**
     * @return resource
     */
    public function stdout(): mixed;

    /**
     * @return resource
     */
    public function stderr(): mixed;

    /**
     * @return resource
     */
    public function stdin(): mixed;
}
