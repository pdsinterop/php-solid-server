# Docker examples

As any PHP application, the PHP Solid server can be run in several ways.

This document gives some simple example of how to run this application with
Apache, Nginx, and the PHP internal HTTP server (for development purposes).

These are just examples. When using the PHP Solid server in prodution, it is
advised to create a custom Docker image to create your specific demands.

## Running HTTPS

The Solid server is expected to run on HTTPS (HTTP+TLS). In order to do so in a
development environment a self-signed certificate is needed.

The Solid server can be run in development with any Docker images on `localhost`
by using the provided (self-signed) certificates in `docker/ssl/`.


## PHP Apache

```sh

docker run                              \
    --expose 443                        \
    --name php-solid-server-apache      \
    --network host                      \
    --rm                                \
    --volume "${PWD}:/app/"             \
    --volume "${PWD}/docker/mods-enabled/:/etc/apache2/mods-enabled/" \
    --volume "${PWD}/docker/server.conf:/etc/apache2/sites-enabled/server.conf" \
    --volume "${PWD}/docker/ssl/:/ssl/" \
    -it                                 \
    php:7.2-apache
```

## Nginx with FPM

```sh
docker run                    \
  -dit                        \
  --name php-solid-server-fpm \
  --network host              \
  --rm                        \
  --volume "${PWD}:/app/"     \
  php:7.2-fpm

docker run                            \
  -dit                                \
  --expose '443:443'                  \
  --name php-solid-server-nginx       \
  --rm                                \
  --volume "${PWD}/docker/nginx-fpm.conf:/etc/nginx/conf.d/default.conf:ro" \
  --volumes-from php-solid-server-fpm \
  nginx
```

## PHP Internal HTTP server

```sh
docker run                        \
  -it                             \
  --expose '80'                   \
  --name php-solid-server-apache  \
  --network host                  \
  --rm                            \
  --volume "${PWD}:/app"          \
  php:7.1-alpine                  \
  php --docroot /app/web/ --server "localhost:80" /app/web/index.php
```

rewrite.load -> ../mods-available/rewrite.load
socache_shmcb.load -> ../mods-available/socache_shmcb.load
ssl.conf -> ../mods-available/ssl.conf
ssl.load -> ../mods-available/ssl.load

  --env 'APACHE_DOCUMENT_ROOT=/var/www/web' \
