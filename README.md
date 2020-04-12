# CorcoViewer

![Project build and test](https://github.com/bobalazek/corcoviewer/workflows/Project%20build%20and%20test/badge.svg)

The CorcoViewer project


## Setup

* Prepare the environment
  * Create your own `.env` file (copy the contents from `.env.example`)
    * All the variables in `.env`, will automatically be forwarded to the `cw_php` container.
    * This is the most convenient way to set the web app variables all in one place. Alternatively you can duplicate the `web/.env` into `web/.env.local` and set your the values for your custom variables there.
  * Create a `docker-compose.override.yml` file and set your custom volumes there - just copy the contents from `docker-compose.override.example.yml`
* Build the app
  * Docker (compose):
    * Run: `docker-compose pull` (pulls down the latest images)
    * Run: `docker-compose up -d` (boots up all the images)
  * Web - Backend:
    * Run: `docker exec -ti cw_php composer install` (installs dependencies)
    * Run: `docker exec -ti cw_php php bin/console doctrine:schema:update --force` (sets up the database schema)
    * Run: `docker exec -ti cw_php php bin/console doctrine:fixtures:load` (loads the fixtures)
  * Web - Frontend:
    * Run: `docker exec -ti cw_node_web yarn install` (installs dependencies)
  * Game:
    * Run: `docker exec -ti cw_node_game npm install` (installs dependencies)
* You are ready to go to the next step - `Development`!


## Development

### Web - Frontend

* Before you start working on the web frontend run: `docker exec -ti cw_node_web yarn run watch`
* Go to your browser and open http://localhost:80 (or whichever port you set in `.env` for the `NGINX_PORT_80` variable)

### Game

* Before you start working on the game run: `docker exec -ti cw_node_game npm run dev`
* Go to your browser and open http://localhost:8080 (or whichever port you set in `.env` for the `GAME_CLIENT_PORT_8080` variable)


## Commands

### Web

#### PHP

* Install composer dependencies: `docker exec -ti cw_php composer install`
* Update composer dependencies: `docker exec -ti cw_php composer update`
* Run database schema update: `docker exec -ti cw_php php bin/console doctrine:schema:update --force`
* Load fixtures: `docker exec -ti cw_php php bin/console doctrine:fixtures:load`
* Run tests: `docker exec -ti cw_php php bin/phpunit`
* Make translations: `docker exec -ti cw_php php bin/console translation:update --dump-messages en --force`
* PHP-CS-Fixer: `docker exec -ti cw_php php-cs-fixer fix`
* Drop database schema: `docker exec -ti cw_php php bin/console doctrine:schema:drop --force`
* Validate database schema: `docker exec -ti cw_php php bin/console doctrine:schema:validate`
* Show database mapping: `docker exec -ti cw_php php bin/console doctrine:mapping:info`
* Run database migrations: `docker exec -ti cw_php php bin/console doctrine:migrations:migrate`
* Lint twig templates: `docker exec -ti cw_php php bin/console lint/twig templates/`
* Messenger - consume: `docker exec -ti cw_php php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger - stop workers: `docker exec -ti cw_php php bin/console messenger:stop-workers`

#### Node

* Install yarn dependencies: `docker exec -ti cw_node_web yarn install`
* Upgrade yarn dependencies: `docker exec -ti cw_node_web yarn upgrade`
* Watch & build static assets (CSS & JS) for development: `docker exec -ti cw_node_web yarn run watch`
* Build static assets (CSS & JS) for production: `docker exec -ti cw_node_web yarn run build`

### Game

* Install npm dependencies: `docker exec -ti cw_node_game npm install`
* Update npm dependencies: `docker exec -ti cw_node_game npm update`
* Watch & build for development: `docker exec -ti cw_node_game npm run dev`
  * Watch & build only client: `docker exec -ti cw_node_game npm run dev-client`
  * Watch & build only server: `docker exec -ti cw_node_game npm run dev-server`
* Build for production: `docker exec -ti cw_node_game npm run build`
