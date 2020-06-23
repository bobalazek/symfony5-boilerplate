# Corcosoft Boilerplate

## Stages

### Development

This is normally the stage in which you work on locally, on your local machine.

* Branch: `develop`
* Database: `local`
* Environment variables
  * APP_ENV=dev
  * NODE_ENV=development

### Testing

 In this stage we do tests for the application. Locally and/or on the CI server.

* Branch: `develop`
* Database: `test` - flushed and recreated during each run from fixtures
* Environment variables
  * APP_ENV=test
  * NODE_ENV=testing

### Nightly/alpha

The exact same environment as production, meant for the first round of testing before it goes into staging/beta.

* Branch: `develop`
* Database: `alpha` - data created with fixtures or obfuscated production data
* Environment variables
  * APP_ENV=prod
  * NODE_ENV=production

### Staging/beta

The exact same environment as production, meant for the final testing before everything goes into production.

* Branch: `master`
* Database: `production`
* Environment variables
  * APP_ENV=prod
  * NODE_ENV=production

### Production/live

The live version for the end-users.

* Branch: `master`
* Database: `production`
* Environment variables
  * APP_ENV=prod
  * NODE_ENV=production
