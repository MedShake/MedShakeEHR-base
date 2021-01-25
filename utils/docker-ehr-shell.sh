#!/bin/bash
docker exec -it $(docker-compose ps -q $1) "/bin/bash"
