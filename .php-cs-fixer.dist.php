<?php

declare(strict_types=1);

const CONFIG = new PhpCsFixer\Config();
const RULES = [
    '@PSR12' => true,
    'strict_param' => true,
    'braces' => false,
    'single_import_per_statement' => false,
    'constant_case' => false, // enums
    'no_break_comment' => false,
    'array_syntax' => ['syntax' => 'short'],
];

CONFIG->setRules(RULES);
CONFIG->setFinder(PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
);

return CONFIG;
