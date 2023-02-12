#!/usr/bin/sh

docker-compose -f docker-compose.yml --project-name 'zupport_assignment' up --build --force-recreate --remove-orphans
