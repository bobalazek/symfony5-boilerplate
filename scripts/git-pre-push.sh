#!/bin/bash

echo -e "========== Start running JS lint ... =========="
docker exec -ti s5bp_node yarn run lint

echo -e "========== Start running PHP lint ... =========="
docker exec -ti s5bp_php composer run-script lint

echo -e "========== Start running PHP tests ... =========="
docker exec -ti s5bp_php composer run-script test

echo -e "========== Start running Cypress tests ... =========="
docker exec -ti s5bp_cypress yarn test"
