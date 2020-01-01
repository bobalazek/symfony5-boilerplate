<?php

// Doctrine schema drop
passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" doctrine:schema:drop --force',
    __DIR__
));

// Doctrine schema update
passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" doctrine:schema:update --force',
    __DIR__
));

// Doctrine fixtures load
passthru(sprintf(
    'APP_ENV=test php "%s/../bin/console" doctrine:fixtures:load -n',
    __DIR__
));

require __DIR__ . '/../config/bootstrap.php';
