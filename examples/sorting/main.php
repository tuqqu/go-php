<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use GoPhp\EnvVarSet;
use GoPhp\Interpreter;
use GoPhp\Stream\StringStreamProvider;

$goRoot = __DIR__;
$goFile = __DIR__ . '/src/main.go';
$goSrc = file_get_contents($goFile);
$stdout = '';

$interp = Interpreter::create(
    source: $goSrc,
    streams: new StringStreamProvider($stdout, $stdout),
    envVars: new EnvVarSet($goRoot)
);

$result = $interp->run();

print "Output:\n$stdout\n";
print "Exit code: {$result->exitCode->value}\n";
