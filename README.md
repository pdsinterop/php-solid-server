# Standalone PHP Solid Server

> Standalone Solid Server written in PHP by PDS Interop

## Solid Server

The Solid specifications defines what makes a "Solid Server". Parts of
those specifications are still likely to change, but at the time of this writing,
they define:

- Authentication
- Authorization (and access control)
- Content representation
- Identity
- Profiles
- Resource (reading and writing) API
- Social Web App Protocols (Notifications, Friends Lists, Followers and Following)

<!--
To read more about Solid, and which IETF and W3C specifications are used, visit: https://pdsinterop.org/solid-specs-overview/
-->

## Available Features

Based on the specifications, the following features are available:

1. User
   - [ ] Authentication
   - [ ] Identity
   - [ ] Profiles
2. Data storage
   - [ ] Content representation
   - [ ] Resource API
     - [ ] HTTP REST API
     - [ ] Websocket API
3. Web Acces Control List
   - [ ] Authorization (and Access Control)
4. Social web apps
   - [ ] Calendar
   - [ ] Contacts
   - [ ] Friends Lists (Followers, Following)
   - [ ] Notifications

The checkboxes show which features are available, and which ones are not.

### Installation

To install the project, clone it from GitHub and install the PHP dependencies
using Composer:

```sh
git clone git://github.com/pdsinerop/solid-server-php.git solid-server-php \
    && cd $ \
    && composer install --no-dev --prefer-dist
```
At this point, the application is ready to run.

## Usage

The PHP Solid server can be run in several different ways.

<!-- @TODO: Add local Dockerfile

The easiest is using the provided `Dockerfile`.

If a different environment is desired, the application can be run with the
Docker image of your choice.

Lastly, the application can be run on a local environment, using Apache, NginX,
or PHP's internal HTTP server. The latter is only advised in development.
 -->

The application can be run with a Docker image of your choice or on a local environment, using Apache, NginX, or PHP's internal HTTP server. The latter is
only advised in development.

<!--
   @TODO: Add single-button deploy scripts/config for Heroku, Glitch, and other
          popular playgrounds/developer oriented service providers.
-->
<!--
### Provided Dockerfile

In the project root, run: `docker run`
-->

### Docker images

When running with your own Docker image, make sure to mount the project folder
to wherever it will be hosted by the Docker container.

For instance:

```
export PORT=80 &&          \
docker run                 \
   --env "PORT=${PORT}"    \
   --expose "${PORT}"      \
   --network host          \
   --rm                    \
   --volume "$PWD:/app"    \
   -it                     \
   php:7.1                 \
   php -S "localhost:${PORT}" -t /app/web/ /app/web/index.php
```

### Local environment

How to run this application in an Apache, NginX, or other popular HTTP servers
falls outside the scope of this project.

For development purposes, the internal PHP HTTP server _is_ explained below.

#### Build-in PHP HTTP server

For development purposes a Composer `serve` command has been provided. This will
run the application using PHP internal HTTP server.

To use it, run `composer serve` in the project root.

**!!! FOR SECURITY REASONS, DO NOT USE THIS METHOD IN PRODUCTION !!!**

By default, the application is hosted on `localhost` port `80`.

Both the `HOST` and `PORT` can be configured before running the command by
setting them in the environment, for instance:

```sh
HOST='solid.local' PORT=8080 composer serve
```

This command can also be run through a docker container, for instance:

```
PORT=8080               \
docker run              \
   --env "PORT=${PORT}" \
   --expose "${PORT}"   \
   --network host       \
   --rm                 \
   --volume "$PWD:/app" \
   -it                  \
   composer:latest      \
   serve
```

<!-- @TODO: Set up email addresses for security and community outreach

## Security

If you discover any security related issues, please email <security@pdsinterop.org> instead of using the issue tracker.

-->

## Contributing

Contributions are welcomed. Read the [contribution guidelines](CONTRIBUTING.md) for
details.

## Change Log

Please see [CHANGELOG](CHANGELOG.md) for details.

## License

All code created by PDS Interop is licensed under the [MIT License][LICENSE].

## Development

### Project structure

This project is structured as follows:

<!--
  .
  ├── build         <- Artifacts created by CI and CLI scripts
  ├── cli           <- CLI scripts
  ├── docs          <- Documentation, hosted at https://pdsinterop.org/solid-server-php/
  ├── src           <- Source code
  ├── tests         <- Unit- and integration-tests
  ├── vendor        <- Third-party and vendor code
  ├── web           <- Web content
  ├── composer.json <- PHP package and dependency configuration
  └── README.md     <- You are now here
-->
```
  .
  ├── src           <- Source code
  ├── vendor        <- Third-party and vendor code
  ├── web           <- Web content
  ├── composer.json <- PHP package and dependency configuration
  └── README.md     <- You are now here
```

<!--
### Coding conventions

You can also run [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) with the configuration file that can be found in the project root directory.

This project comes with a configuration file and an executable for [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) (`.php_cs`) that you can use to (re)format your sourcecode for compliance with this project's coding guidelines:

```sh
$ composer php-cs-fixer fix
```

### Testing

The PHPUnit version to be used is the one installed as a `dev-` dependency via composer. It can be run using `composer test` or by calling it directly:

```sh
$ ./vendor/bin/phpunit
```
-->
