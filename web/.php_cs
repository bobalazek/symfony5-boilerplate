<?php

$finder = PhpCsFixer\Finder::create()
  ->exclude('node_modules')
  ->exclude('var')
  ->exclude('src/DataFixtures/data')
  ->in(__DIR__)
;

return PhpCsFixer\Config::create()
  ->setRules([
    '@PhpCsFixer' => true,
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],
    'concat_space' => ['spacing' => 'one'],
  ])
  ->setFinder($finder)
;
