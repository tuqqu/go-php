<?php

declare(strict_types=1);

const config = new PhpCsFixer\Config();
const rules = [
    '@PER-CS' => true,
    'strict_param' => true,
    'no_unused_imports' => true,
    'array_syntax' => ['syntax' => 'short'],
    'single_line_empty_body' => true,
    'statement_indentation' => false,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
];

config->setRules(rules);
config->setFinder(PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/bin')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
);

return config;
