# Symfony5 boilerplate

## Setup

* Prepare the environment
  * Create your own `.env` file (copy the contents from `.env.example`)
    * All the variables in `.env`, will automatically be forwarded to the `s5bp_php` container.
    * This is the most convenient way to set the web app variables all in one place. Alternatively you can duplicate the `app/.env` into `app/.env.local` and set your the values for your custom variables there.
  * Create a `docker-compose.override.yml` file and set your custom volumes there - just copy the contents from `docker-compose.override.example.yml`
* Build the app
  * Docker (compose):
    * Run: `docker-compose up -d` (prepares all the containers)
  * App - Backend:
    * Run: `docker exec -ti s5bp_php composer install` (installs dependencies)
    * Run: `docker exec -ti s5bp_php composer run-script database-recreate` (drops the schema, updates the schema and loads the fixtures)
  * App - Frontend:
    * Run: `docker exec -ti s5bp_node yarn install` (installs dependencies)
    * Run: `docker exec -ti s5bp_node yarn run build` (build the frontend app)
  * App - E2E Testing:
    * Run: `docker exec -ti s5bp_cypress yarn install` (installs dependencies)
* You are ready to go to the next step - [Development](development.md)!
