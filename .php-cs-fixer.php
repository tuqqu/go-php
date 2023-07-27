<?php

declare(strict_types=1);

const CONFIG = new PhpCsFixer\Config();
const RULES = [
    '@PER' => true,
    'strict_param' => true,
    'single_import_per_statement' => false,
    'no_unused_imports' => true,
    'array_syntax' => ['syntax' => 'short'],
    'single_line_empty_body' => true,
    'statement_indentation' => false,
];

CONFIG->setRules(RULES);
CONFIG->setFinder(PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/bin')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
);

return CONFIG;
