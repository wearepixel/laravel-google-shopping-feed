<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('bootstrap/cache')
    ->exclude('storage')
    ->exclude('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$rules = [
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['operators' => ['=>' => null]],
    'linebreak_after_opening_tag' => true,
    'ordered_imports' => ['sort_algorithm' => 'length'],
    'phpdoc_order' => true,
    'phpdoc_no_empty_return' => false,
    'yoda_style' => false,
    'no_unused_imports' => true,
];

$config = new PhpCsFixer\Config();

return $config->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(false);
