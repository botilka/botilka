<?php

declare(strict_types=1);

$rules = [
    '@PHP80Migration:risky' => true,
    '@PHP82Migration' => true,
        '@PHPUnit100Migration:risky' => true,
'@PSR2' => true,
    '@PhpCsFixer' => true,
    '@PhpCsFixer:risky' => true,
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'header_comment' => ['header' => ''],
    'native_function_invocation' => true,
    'phpdoc_to_comment' => false,
    'php_unit_test_class_requires_covers' => false,
    'static_lambda' => true,
];

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
