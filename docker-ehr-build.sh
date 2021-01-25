#!/bin/sh
docker build -t medshake-ehr-db:latest . -f Dockerfile-db
docker build -t medshake-ehr-web:latest . -f Dockerfile-web
