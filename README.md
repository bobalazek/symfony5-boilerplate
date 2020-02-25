# CorcoViewer

The CorcoViewer project


## Setup

* Prepare the environment
  * Create your own `.env` file (copy the contents from `.env.example`)
    * All the variables in `.env`, will automatically be forwarded to the `cw_phpfpm` container.
    * This is the most convenient way to set the web app variables all in one place. Alternatively you can duplicate the `web/.env` into `web/.env.local` and set your the values for your custom variables there.
  * Create a `docker-compose.override.yml` file and set your custom volumes there - just copy the contents from `docker-compose.override.example.yml`
* Build the app
  * Docker (compose):
    * Run: `docker-compose up -d`
  * Web - Backend:
    * Run: `docker exec -ti cw_phpfpm composer install`
    * Run: `docker exec -ti cw_phpfpm php bin/console doctrine:schema:update --force`
    * Run: `docker exec -ti cw_phpfpm php bin/console doctrine:fixtures:load`
  * Web - Frontend:
    * Run: `docker exec -ti cw_node_web yarn install`
  * Game:
    * Run: `docker exec -ti cw_node_game npm install`
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

#### PHP-FPM

* Install composer dependencies: `docker exec -ti cw_phpfpm composer install`
* Update composer dependencies: `docker exec -ti cw_phpfpm composer install`
* Run schema update: `docker exec -ti cw_phpfpm php bin/console doctrine:schema:update --force`
* Load fixtures: `docker exec -ti cw_phpfpm php bin/console doctrine:fixtures:load`
* Run tests: `docker exec -ti cw_phpfpm php bin/phpunit`
* Make translations: `docker exec -ti cw_phpfpm php bin/console translation:update --dump-messages en --force`
* PHP-CS-Fixer: `docker exec -ti cw_phpfpm php-cs-fixer fix`
* Drop schema: `docker exec -ti cw_phpfpm php bin/console doctrine:schema:drop --force`
* Messenger - consume: `docker exec -ti cw_phpfpm php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger - stop workers: `docker exec -ti cw_phpfpm php bin/console messenger:stop-workers`

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
