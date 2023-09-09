<?php

declare(strict_types=1);

namespace GoPhp\Error;

use GoParser\Error;
use GoParser\Lexer\LexError;
use GoParser\Lexer\Position;
use GoParser\SyntaxError;

use function sprintf;

final class ParserError implements GoError
{
    public function __construct(
        private readonly Position $position,
        private readonly string $message,
    ) {}

    public static function fromInternalParserError(Error $error): self
    {
        return new self(
            match (true) {
                $error instanceof LexError => $error->pos,
                $error instanceof SyntaxError => $error->pos,
                default => throw new InternalError(sprintf('unknown error type %s', $error::class)),
            },
            (string) $error,
        );
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
