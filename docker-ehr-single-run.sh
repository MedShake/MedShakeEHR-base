#!/bin/sh
#docker run --privileged -p 80:80 -p 8080:8080 -it medshake-ehr-base:latest
docker run --name medshake-ehr-single --privileged -it -p 80:80 -p 8080:8080 --mount type=bind,source=$HOME/ehr,target=/root/ehr medshake-ehr-base:latest 


