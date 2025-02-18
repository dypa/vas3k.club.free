<?php

$finder = new PhpCsFixer\Finder();
$config = new PhpCsFixer\Config();

$finder->in(__DIR__ . '/src');
$finder->exclude('var');
$finder->exclude('vendor');

$config->setRules([
    '@Symfony' => true,
    '@PHP83Migration' => true,
    'concat_space' => ['spacing' => 'one'],
]);

$config->setFinder($finder);

return $config;
