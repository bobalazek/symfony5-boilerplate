<?php

$finder = PhpCsFixer\Finder::create()
  ->exclude('node_modules')
  ->exclude('var')
  ->exclude('src/DataFixtures/data')
  ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config
  ->setRules([
    '@PhpCsFixer' => true,
    '@Symfony' => true,
    'array_syntax' => ['syntax' => 'short'],
    'concat_space' => ['spacing' => 'one'],
    'operator_linebreak' => [
      'only_booleans' => true,
      'position' => 'end',
    ],
    // For some strange reason that strips the additional "*"
    // in comments like /** @var Post $post */ so we need to disable it
    'phpdoc_to_comment' => false,
  ])
  ->setFinder($finder)
;
