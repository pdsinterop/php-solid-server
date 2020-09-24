# Standalone PHP Solid Server

[![PDS Interop][pdsinterop-shield]][pdsinterop-site]
[![Project stage: Development][project-stage-badge: Development]][project-stage-page]
[![License][license-shield]][license-link]
[![Latest Version][version-shield]][version-link]
[![standard-readme compliant][standard-readme-shield]][standard-readme-link]
![Maintained][maintained-shield]

_Standalone Solid Server written in PHP by PDS Interop_

## Table of Contents

<!-- toc -->
<!-- tocstop -->

## Background

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

### Installation

To install the project, clone it from GitHub and install the PHP dependencies
using Composer:

```sh
git clone git://github.com/pdsinterop/php-solid-server.git \
    && cd php-solid-server \
    && composer install --no-dev --prefer-dist
```
At this point, the application is ready to run.

## Usage

The PHP Solid server can be run in several different ways.

<!-- @TODO: Add local Dockerfile  -->

The application can be run with a Docker image of your choice or on a local
environment, using Apache, NginX, or PHP's internal HTTP server. The latter is
only advised in development.

For security reasons, the server expects to run on HTTPS (also known as HTTP+TLS).

To run insecure, set the environment variable `ENVIRONMENT` to `develop`. This
will prohibit the application from running in production mode.

<!--
   @TODO: Add single-button deploy scripts/config for Heroku, Glitch, and other
          popular playgrounds/developer oriented service providers.
-->

### Docker images

When running with your own Docker image, make sure to mount the project folder
to wherever the image expects it to be, e.g. `/app` or `/var/www`.

For instance:

```
export PORT=8080 &&        \
docker run                 \
   --env "PORT=${PORT}"    \
   --expose "${PORT}"      \
   --network host          \
   --rm                    \
   --volume "$PWD:/app"    \
   -it                     \
   php:7.1                 \
   php --docroot /app/web/ --server "localhost:${PORT}" /app/web/index.php
```
Or on Mac:
```
export PORT=8080 &&        \
docker run                 \
   --env "PORT=${PORT}"    \
   --expose "${PORT}"      \
   -p "${PORT}:${PORT}"    \
   --rm                    \
   --volume "$PWD:/app"    \
   -it                     \
   php:7.1                 \
   php --docroot /app/web/ --server "localhost:${PORT}" /app/web/index.php
```


### Local environment

How to run this application in an Apache, NginX, or other popular HTTP servers
falls outside the scope of this project.

For development purposes, the internal PHP HTTP server _is_ explained below.

#### Built-in PHP HTTP server

For development purposes a Composer `serve-dev` command has been provided. This will
run the application using PHP internal HTTP server.

To use it, run `composer serve-dev` in the project root.

**!!! FOR SECURITY REASONS, DO NOT USE THIS METHOD IN PRODUCTION !!!**

By default, the application is hosted on `localhost` port `80`.

Both the `HOST` and `PORT` can be configured before running the command by
setting them in the environment, for instance:

```sh
HOST='solid.local' PORT=8080 composer serve
```

This command can also be run through a docker container, for instance:

```
export PORT=8080 &&     \
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

## Contributing

Questions or feedback can be given by [opening an issue on GitHub](https://github.com/pdsinterop/flysystem-rdf/issues).

All PDS Interop projects are open source and community-friendly. 
Any contribution is welcome!
For more details read the [contribution guidelines](contributing.md).

All PDS Interop projects adhere to [the Code Manifesto](http://codemanifesto.com)
as its [code-of-conduct](CODE_OF_CONDUCT.md). Contributors are expected to abide by its terms.

There is [a list of all contributors on GitHub][contributors-page].

For a list of changes see the [CHANGELOG](CHANGELOG.md) or the GitHub releases page.

## License

All code created by PDS Interop is licensed under the [MIT License][license-link].

[contributors-page]:  https://github.com/pdsinterop/flysystem-rdf/contributors
[license-link]: ./LICENSE
[license-shield]: https://img.shields.io/github/license/pdsinterop/flysystem-rdf.svg
[maintained-shield]: https://img.shields.io/maintenance/yes/2020
[pdsinterop-shield]: https://img.shields.io/badge/-PDS%20Interop-gray.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii01IC01IDExMCAxMTAiIGZpbGw9IiNGRkYiIHN0cm9rZS13aWR0aD0iMCI+CiAgICA8cGF0aCBkPSJNLTEgNTJoMTdhMzcuNSAzNC41IDAgMDAyNS41IDMxLjE1di0xMy43NWEyMC43NSAyMSAwIDAxOC41LTQwLjI1IDIwLjc1IDIxIDAgMDE4LjUgNDAuMjV2MTMuNzVhMzcgMzQuNSAwIDAwMjUuNS0zMS4xNWgxN2EyMiAyMS4xNSAwIDAxLTEwMiAweiIvPgogICAgPHBhdGggZD0iTSAxMDEgNDhhMi43NyAyLjY3IDAgMDAtMTAyIDBoIDE3YTIuOTcgMi44IDAgMDE2OCAweiIvPgo8L3N2Zz4K
[pdsinterop-site]: https://pdsinterop.org/
[project-stage-badge: Development]: https://img.shields.io/badge/Project%20Stage-Development-yellowgreen.svg
[project-stage-page]: https://blog.pother.ca/project-stages/
[standard-readme-link]: https://github.com/RichardLitt/standard-readme
[standard-readme-shield]: https://img.shields.io/badge/readme%20style-standard-brightgreen.svg
[version-link]: https://packagist.org/packages/pdsinterop/flysystem-rdf
[version-shield]: https://img.shields.io/github/v/release/pdsinterop/flysystem-rdf?sort=semver
