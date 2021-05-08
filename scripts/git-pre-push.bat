@ECHO OFF

ECHO ========== Start running JS lint ... ==========
docker exec -ti mnc_node yarn run lint

ECHO ========== Start running PHP lint ... ==========
docker exec -ti mnc_php composer run-script lint

ECHO ========== Start running PHP tests ... ==========
docker exec -ti mnc_php composer run-script test

ECHO ========== Start running Cypress tests ... ==========
docker exec -ti s5bp_cypress yarn test"
