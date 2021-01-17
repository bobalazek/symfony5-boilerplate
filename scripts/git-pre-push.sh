#!/bin/bash

echo -e "========== Start running JS lint ... =========="
docker exec -ti mnc_node yarn run lint

echo -e "========== Start running PHP lint ... =========="
docker exec -ti mnc_php composer run-script lint

echo -e "========== Start running PHP tests ... =========="
docker exec -ti mnc_php composer run-script test
