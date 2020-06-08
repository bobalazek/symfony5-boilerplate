# CorcoViewer

## Commands

### Web

#### PHP

* Install composer dependencies: `docker exec -ti cw_php composer install`
* Update composer dependencies: `docker exec -ti cw_php composer update`
* Run database schema update: `docker exec -ti cw_php php bin/console doctrine:schema:update --force`
* Run tests: `docker exec -ti cw_php php bin/phpunit`
* PHP-CS-Fixer: `docker exec -ti cw_php php-cs-fixer fix`
* Make translations: `docker exec -ti cw_php php bin/console translation:update --dump-messages en --force`
* Load fixtures: `docker exec -ti cw_php php bin/console doctrine:fixtures:load`
* Drop database schema: `docker exec -ti cw_php php bin/console doctrine:schema:drop --force`
* Validate database schema: `docker exec -ti cw_php php bin/console doctrine:schema:validate`
* Show database mapping: `docker exec -ti cw_php php bin/console doctrine:mapping:info`
* Run database migrations: `docker exec -ti cw_php php bin/console doctrine:migrations:migrate`
* Lint twig templates: `docker exec -ti cw_php php bin/console lint:twig templates/`
* Lint YAML config: `docker exec -ti cw_php php bin/console lint:yaml config/`
* Lint XLIFF translations: `docker exec -ti cw_php php bin/console lint:xliff translations/`
* Messenger queue consume: `docker exec -ti cw_php php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger queue stop workers: `docker exec -ti cw_php php bin/console messenger:stop-workers`

#### Node

* Install yarn dependencies: `docker exec -ti cw_node_web yarn install`
* Upgrade yarn dependencies: `docker exec -ti cw_node_web yarn upgrade`
* Watch & build static assets (CSS & JS) for development: `docker exec -ti cw_node_web yarn run watch`
* Build static assets (CSS & JS) for production: `docker exec -ti cw_node_web yarn run build`

### Game

* Install npm dependencies: `docker exec -ti cw_node_game npm install`
* Update npm dependencies: `docker exec -ti cw_node_game npm update`
* Watch & build for development: `docker exec -ti cw_node_game npm start`
  * Watch & build only client: `docker exec -ti cw_node_game npm run start-client`
  * Watch & build only server: `docker exec -ti cw_node_game npm run start-server`
* Build for production: `docker exec -ti cw_node_game npm run build`
