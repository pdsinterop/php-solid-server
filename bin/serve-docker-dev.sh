#!/usr/bin/env bash

# Run a PHP development server in docker

docker run                          \
  -i                                \
  --expose 443                      \
  --name server                     \
  --network host                    \
  --rm                              \
  --volume "${PWD}:/app"            \
  "${DOCKER_IMAGE:=php-solid-server}"
