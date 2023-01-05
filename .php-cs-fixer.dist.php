<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude('var')
    ->in(__DIR__)
    ->name('*.php')
    ->notPath('deploy.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR2' => true,
    '@Symfony' => true,
    'constant_case' => [
        'case' => 'lower',
    ],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
    ],
    'single_class_element_per_statement' => [
        'elements' => ['property'],
    ],
])
    ->setFinder($finder);
