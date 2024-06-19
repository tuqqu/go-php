<?php

declare(strict_types=1);

namespace GoPhp\Debug;

use GoParser\Lexer\Position;

use function realpath;
use function sprintf;

use const STDERR;

/**
 * Dumps the call stack to STDERR.
 */
function dump_call_stack(CallStack $callStack): void
{
    $positionDumper = fn(?Position $position): string => $position === null
        ? 'unknown'
        : sprintf(
            "%s:%d:%d",
            $position->filename === null
                ? ''
                : realpath($position->filename),
            $position->line,
            $position->offset,
        );

    $callTraces = $callStack->getCallTraces();
    $length = $callStack->count();

    for ($i = 0; $i < $length; $i++) {
        if ($i === 0) {
            continue;
        }

        fwrite(STDERR, sprintf(
            "%s(...)\n\t%s\n",
            $callTraces[$i]->name,
            $positionDumper($callTraces[$i - 1]->position),
        ));
    }
}
