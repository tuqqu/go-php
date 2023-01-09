<?php

declare(strict_types=1);

const CONFIG = new PhpCsFixer\Config();
const RULES = [
    '@PSR12' => true,
    'strict_param' => true,
    'braces' => false,
    'single_import_per_statement' => false,
    'no_break_comment' => false,                // false positive with match
    'no_unused_imports' => true,
    'array_syntax' => ['syntax' => 'short'],
];

CONFIG->setRules(RULES);
CONFIG->setFinder(PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/bin')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
);

return CONFIG;
