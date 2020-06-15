# Corcoviewer

## Setup

* Prepare the environment
  * Create your own `.env` file (copy the contents from `.env.example`)
    * All the variables in `.env`, will automatically be forwarded to the `cw_php` container.
    * This is the most convenient way to set the web app variables all in one place. Alternatively you can duplicate the `app/.env` into `app/.env.local` and set your the values for your custom variables there.
  * Create a `docker-compose.override.yml` file and set your custom volumes there - just copy the contents from `docker-compose.override.example.yml`
* Build the app
  * Docker (compose):
    * Run: `docker-compose pull` (pulls down the latest images)
    * Run: `docker-compose build` (builds the images)
    * Run: `docker-compose up -d` (boots up all the images)
  * App - Backend:
    * Run: `docker exec -ti cw_php composer install` (installs dependencies)
    * Run: `docker exec -ti cw_php php bin/console doctrine:schema:update --force` (sets up the database schema)
    * Run: `docker exec -ti cw_php php bin/console doctrine:fixtures:load` (loads the fixtures)
  * App - Frontend:
    * Run: `docker exec -ti cw_node yarn install` (installs dependencies)
* You are ready to go to the next step - [Development](development.md)!
