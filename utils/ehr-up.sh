#!/bin/bash
SCRIPT_PATH=$(dirname `which $0`)
. $SCRIPT_PATH/env.sh
docker-compose up -d
#docker-compose up
