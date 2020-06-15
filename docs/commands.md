# Corcoviewer

## Commands

### PHP

* Install composer dependencies: `docker exec -ti cw_php composer install`
* Update composer dependencies: `docker exec -ti cw_php composer update`
* Update database schema: `docker exec -ti cw_php php bin/console doctrine:schema:update --force`
* Drop database schema: `docker exec -ti cw_php php bin/console doctrine:schema:drop --force`
* Validate database schema: `docker exec -ti cw_php php bin/console doctrine:schema:validate`
* Show database mapping: `docker exec -ti cw_php php bin/console doctrine:mapping:info`
* Run database migrations: `docker exec -ti cw_php php bin/console doctrine:migrations:migrate`
* Load fixtures: `docker exec -ti cw_php php bin/console doctrine:fixtures:load`
* Make translations: `docker exec -ti cw_php php bin/console translation:update --dump-messages en --force`
* Lint twig template files: `docker exec -ti cw_php php bin/console lint:twig templates/`
* Lint YAML config files: `docker exec -ti cw_php php bin/console lint:yaml config/`
* Lint XLIFF translation files: `docker exec -ti cw_php php bin/console lint:xliff translations/`
* Lint PHP files: `docker exec -i cw_php php-cs-fixer fix --dry-run`
* Run PHP-CS-Fixer: `docker exec -ti cw_php php-cs-fixer fix`
* Run tests: `docker exec -ti cw_php php bin/phpunit`
* Messenger queue consume: `docker exec -ti cw_php php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger queue stop workers: `docker exec -ti cw_php php bin/console messenger:stop-workers`

### Node

* Install yarn dependencies: `docker exec -ti cw_node yarn install`
* Upgrade yarn dependencies: `docker exec -ti cw_node yarn upgrade`
* Watch & build static assets (CSS & JS) for development: `docker exec -ti cw_node yarn run watch`
* Build static assets (CSS & JS) for production: `docker exec -ti cw_node yarn run build`
* Lint JS files: `docker exec -ti cw_node yarn run lint`
* Lint & fix JS files: `docker exec -ti cw_node yarn run lint-fix`
