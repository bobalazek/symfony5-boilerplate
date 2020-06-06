<?php

$appEnv = $_ENV['APP_ENV'] ?? 'test';

// Cache clear
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
    $appEnv,
    __DIR__
));

// Doctrine schema drop
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" doctrine:schema:drop --force',
    $appEnv,
    __DIR__
));

// Doctrine schema update
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" doctrine:schema:update --force',
    $appEnv,
    __DIR__
));

// Doctrine fixtures load
passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" doctrine:fixtures:load -n',
    $appEnv,
    __DIR__
));

require __DIR__ . '/../config/bootstrap.php';
