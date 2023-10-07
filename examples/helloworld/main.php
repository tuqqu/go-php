<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use GoPhp\Interpreter;
use GoPhp\Stream\StringStreamProvider;

$stdout = '';
$interp = Interpreter::create(
    source: <<<'GO'
        package main
        
        func main() {
            println("Hello, World!")
        }
    GO,
    streams: new StringStreamProvider($stdout, $stdout),
);

$result = $interp->run();

print "Output:\n$stdout\n";
print "Exit code: $result->exitCode->value\n";
