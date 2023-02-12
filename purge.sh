#!/usr/bin/sh

docker system prune -af
docker volume prune -f
docker network prune -f
