<?php

$finder = new PhpCsFixer\Finder();
$config = new PhpCsFixer\Config();

$config->setRiskyAllowed(true);

$finder->in(__DIR__ . '/src');
$finder->exclude('var');
$finder->exclude('vendor');

$config->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    '@PHP84Migration' => true,
    'concat_space' => ['spacing' => 'one'],
    'native_function_invocation' => true,
]);

$config->setFinder($finder);

return $config;
