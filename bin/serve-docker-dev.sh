#!/usr/bin/env bash

# Run a PHP development server in docker

docker run                          \
  -i                                \
  --env 'ENVIRONMENT=development'   \
  --expose 80                       \
  --name php-solid-server           \
  --network host                    \
  --rm                              \
  --volume "${PWD}:/app"            \
  "php:${PHP_VERSION:-7.1}-alpine"  \
  php                               \
    --define 'log_errors=On'        \
    --docroot /app/web/             \
    --server '0.0.0.0:80'           \
    /app/web/index.php
