<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config('standards'))
    ->setFinder(
        (new Finder())->in([
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ])
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PHP80Migration' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'yoda_style' => false,
        'phpdoc_add_missing_param_annotation' => false,
        'no_superfluous_phpdoc_tags' => true,
        'general_phpdoc_annotation_remove' => [
            'annotations' => [
                'author',
                'inheritdoc',
                'inheritDoc',
                'package',
                'subpackage',
                'version',
            ],
        ],
        'phpdoc_to_comment' => false,
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'self',
        ],
        'php_unit_internal_class' => false,
        'php_unit_test_annotation' => [
            'style' => 'annotation',
        ],
    ])
;
