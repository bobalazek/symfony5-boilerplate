# The Wall

The The Wall project


## Setup

* Prepare the environment
  * Create your own `.env` file (copy the contents from `.env.example`)
    * All the variables in `.env`, will automatically be forwarded to the `tw_phpfpm` container.
    * This is the most convenient way to set the web app variables all in one place. Alternatively you can duplicate the `web/.env` into `web/.env.local` and set your the values for your custom variables there - particularly those, inside the `Project` block.
  * Create a `docker-compose.override.yml` file and set your custom volumes there - just copy the contents from `docker-compose.override.example.yml`
* Build the app
  * Docker (compose):
    * Run: `docker-compose up -d`
  * Backend:
    * Run: `docker exec -ti tw_phpfpm composer install`
    * Run: `docker exec -ti tw_phpfpm php bin/console doctrine:schema:update --force`
    * Run: `docker exec -ti tw_phpfpm php bin/console doctrine:fixtures:load`
  * Frontend:
    * Run: `docker exec -ti tw_node yarn install`
    * Run: `docker exec -ti tw_node yarn run build`
* You are ready to go!


## Commands

### Web

* Watch & build static assets (CSS & JS) for development: `docker exec -ti tw_node yarn run watch`
* Build static assets (CSS & JS) for production: `docker exec -ti tw_node yarn run build`
* Run tests: `docker exec -ti tw_phpfpm php bin/phpunit`
* Make translations: `docker exec -ti tw_phpfpm php bin/console translation:update --dump-messages en --force`
* PHP-CS-Fixer: `docker exec -ti tw_phpfpm php-cs-fixer fix`
* Drop schema: `docker exec -ti tw_phpfpm php bin/console doctrine:schema:drop --force`
* Messenger - consume: `docker exec -ti tw_phpfpm php bin/console messenger:consume async -vvv --time-limit=3600`
* Messenger - stop workers: `docker exec -ti tw_phpfpm php bin/console messenger:stop-workers`
