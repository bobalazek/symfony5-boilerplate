# Symfony5 boilerplate

## Commands

### PHP

* Install composer dependencies: `docker exec -ti s5bp_php composer install`
* Update composer dependencies: `docker exec -ti s5bp_php composer update`
* Update database schema: `docker exec -ti s5bp_php php bin/console doctrine:schema:update --force`
* Drop database schema: `docker exec -ti s5bp_php php bin/console doctrine:schema:drop --force`
* Validate database schema: `docker exec -ti s5bp_php php bin/console doctrine:schema:validate`
* Show database mapping: `docker exec -ti s5bp_php php bin/console doctrine:mapping:info`
* Run database migrations: `docker exec -ti s5bp_php php bin/console doctrine:migrations:migrate`
* Load fixtures: `docker exec -ti s5bp_php php bin/console doctrine:fixtures:load --no-interaction`
* Generate/update translations: `docker exec -ti s5bp_php php bin/console translation:update --dump-messages en --force`
* Lint twig template files: `docker exec -ti s5bp_php php bin/console lint:twig templates/`
* Lint YAML config files: `docker exec -ti s5bp_php php bin/console lint:yaml config/`
* Lint XLIFF translation files: `docker exec -ti s5bp_php php bin/console lint:xliff translations/`
* Lint PHP files: `docker exec -i s5bp_php php-cs-fixer fix --dry-run --diff`
* Lint PHP files with PHPStan: `docker exec -ti s5bp_php vendor/bin/phpstan analyse -c phpstan.neon`
* Run PHP-CS-Fixer: `docker exec -ti s5bp_php php-cs-fixer fix`
* Run PHPUnit tests: `docker exec -ti s5bp_php php bin/phpunit`
* Messenger queue consume: `docker exec -ti s5bp_php php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger queue stop workers: `docker exec -ti s5bp_php php bin/console messenger:stop-workers`

#### Quick commands

* Recreate the database: `docker exec -ti s5bp_php composer run-script database-recreate`
  * Drops the schema, updates the schema & loads the fixtures
* Lint: `docker exec -ti s5bp_php composer run-script lint`
  * Lints YAML config files, XLIFF translations files & PHP files
* Test: `docker exec -ti s5bp_php composer run-script test`
  * Runs PHPUnit tests
* PHP-CS-Fix: `docker exec -ti s5bp_php composer run-script php-cs-fix`
  * Runs PHP-CS-Fixer
* Generate translations: `docker exec -ti s5bp_php composer run-script translations-generate`
  * Generates the translations

### Node

* Install yarn dependencies: `docker exec -ti s5bp_node yarn install`
* Upgrade yarn dependencies: `docker exec -ti s5bp_node yarn upgrade`
* Watch & build static assets (CSS & JS) for development: `docker exec -ti s5bp_node yarn run watch`
* Build static assets (CSS & JS) for production: `docker exec -ti s5bp_node yarn run build`
* Lint JS files: `docker exec -ti s5bp_node yarn run lint`
* Lint & fix JS files: `docker exec -ti s5bp_node yarn run lint-fix`
