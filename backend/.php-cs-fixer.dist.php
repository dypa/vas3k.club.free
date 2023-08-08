<?php

$finder = new PhpCsFixer\Finder();
$config = new PhpCsFixer\Config();

$finder->in(__DIR__);
$finder->exclude('var');
$finder->exclude('vendor');

$config->setRules([
    '@Symfony' => true,
]);

$config->setFinder($finder);

return $config;