# Standalone PHP Solid Server

[![Project stage: Development][project-stage-badge: Development]][project-stage-page]
[![License][license-shield]][license-link]
[![Latest Version][version-shield]][version-link]
![Maintained][maintained-shield]

[![PDS Interop][pdsinterop-shield]][pdsinterop-site]
[![standard-readme compliant][standard-readme-shield]][standard-readme-link]
[![keep-a-changelog compliant][keep-a-changelog-shield]][keep-a-changelog-link]

_Standalone Solid Server written in PHP by PDS Interop_

## Table of Contents

<!-- toc -->

- [Background](#background)
- [Installation](#installation)
- [Usage](#usage)
  - [Docker images](#docker-images)
  - [Local environment](#local-environment)
    - [Built-in PHP HTTP server](#built-in-php-http-server)
- [Security](#security)
- [Running solid/webid-provider-tests](#running-solidwebid-provider-tests)
- [Available Features](#available-features)
- [Development](#development)
  - [Project structure](#project-structure)
  - [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

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

## Installation

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

The application can be run with a Docker image of your choice or on a local
environment, using Apache, NginX, or PHP's internal HTTP server. The latter is
only advised in development.

For security reasons, the server expects to run on HTTPS (also known as HTTP+TLS).

To run insecure, for instance when the application is run behind a proxy or in a
PHP-FPM (or similar) setup, set the environment variable `PROXY_MODE`.
This will allow the application to accept HTTP requests.

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
   php:7.3                 \
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
   php:7.3                 \
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

By default, the application is hosted on `localhost` port `8080`.
So if you visit http://localhost:8080/ with your browser, you should see "Hello, World!".

Both the `HOST` and `PORT` can be configured before running the command by
setting them in the environment, for instance:

```sh
HOST='solid.local' PORT=1234 composer serve-dev
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

## Running solid/webid-provider-tests
Due to https://github.com/pdsinterop/php-solid-server/issues/8 you should run, in one terminal window:
```sh
HOST=127.0.0.1 composer serve-dev
```
and in another you run the [webid-provider-test](https://github.com/solid/webid-provider-tests) as:
```sh
SERVER_ROOT=http://localhost:8080 ./node_modules/.bin/jest test/surface/fetch-openid-config.test.ts
```
The current `dev` branch of php-solid-server should pass roughly 7 out of 17 tests.

## Available Features

Based on the specifications, the features listed below _should_ be available.

The checkboxes show which features _are_, and which ones _are not_.

The underlying functionality for these features is provided by:

- <sup>[auth]</sup> = [`pdsinterop/solid-auth`](https://github.com/pdsinterop/php-solid-auth)
- <sup>[crud]</sup> = [`pdsinterop/solid-crud`](https://github.com/pdsinterop/php-solid-crud)
- <sup>[p/s]</sup> = [`pdsinterop/solid-pubsub-server`](https://github.com/pdsinterop/php-solid-pubsub-server)
- <sup>[rdf]</sup> = [`pdsinterop/flysystem-rdf`](https://github.com/pdsinterop/flysystem-rdf)

1. User
   - [x] Authentication <sup>[auth]</sup> (since **v0.3**)
   - [x] Identity (since **v0.2**)
   - [x] Profiles (since **v0.2**)
2. Data storage
   - [x] Content representation <sup>[rdf]</sup> (since **v0.4**)
   - [x] Resource API
     - [x] HTTP REST API <sup>[crud]</sup> (since **v0.4**)
     - [x] Websocket API <sup>[p/s]</sup> (since **v0.6**)
3. Web Acces Control List
   - [x] Authorization (and Access Control) <sup>[crud]</sup> (since **v0.6**)
4. Social web apps
   - [ ] Calendar
   - [ ] Contacts
   - [ ] Friends Lists (Followers, Following)
   - [ ] Notifications
5. Extra / Non-specification features
   - [x] Solid [link-metadata] <sup>[crud]</sup> (since **v0.7**)

[link-metadata]: https://github.com/pdsinterop/solid-link-metadata/

## Development

The easiest way to develop this project is by running the environment provided
by the `docker-compose.yml` file. This can be done by running `docker-compose up`.

This will start the application and a pubsub server in separate docker containers.

### Project structure

This project is structured as follows:

```
  .
  ├── bin/          <- CLI scripts
  ├── config/       <- Empty directory where server configuration is generated
  ├── docs/         <- Documentation
  ├── src/          <- Source code
  ├── tests/        <- Test fixtures, Integration- and unit-tests
  ├── vendor/       <- Third-party and vendor code
  ├── web/          <- Web content
  ├── composer.json <- PHP package and dependency configuration
  └── README.md     <- You are now here
```

### Testing

The PHPUnit version to be used is the one installed as a `dev-` dependency via composer. It can be run using `composer test` or by calling it directly:

```sh
$ ./bin/phpunit
```

## Contributing

Questions or feedback can be given by [opening an issue on GitHub][issues-link].

All PDS Interop projects are open source and community-friendly.
Any contribution is welcome!
For more details read the [contribution guidelines][contributing-link].

All PDS Interop projects adhere to [the Code Manifesto](http://codemanifesto.com)
as its [code-of-conduct][code-of-conduct]. Contributors are expected to abide by its terms.

There is [a list of all contributors on GitHub][contributors-page].

For a list of changes see the [CHANGELOG][changelog] or [the GitHub releases page][releases-page].

## License

All code created by PDS Interop is licensed under the [MIT License][license-link].

[changelog]: CHANGELOG.md
[code-of-conduct]: CODE_OF_CONDUCT.md
[contributing-link]: CONTRIBUTING.md
[contributors-page]: https://github.com/pdsinterop/php-solid-server/contributors
[issues-link]: https://github.com/pdsinterop/php-solid-server/issues
[releases-page]: https://github.com/pdsinterop/php-solid-server/releases
[keep-a-changelog-link]: https://keepachangelog.com/
[keep-a-changelog-shield]: https://img.shields.io/badge/Keep%20a%20Changelog-f15d30.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmZmYiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K
[license-link]: ./LICENSE
[license-shield]: https://img.shields.io/github/license/pdsinterop/php-solid-server.svg
[maintained-shield]: https://img.shields.io/maintenance/yes/2022.svg
[pdsinterop-shield]: https://img.shields.io/badge/-PDS%20Interop-7C4DFF.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9Ii01IC01IDExMCAxMTAiIGZpbGw9IiNGRkYiIHN0cm9rZS13aWR0aD0iMCI+CiAgICA8cGF0aCBkPSJNLTEgNTJoMTdhMzcuNSAzNC41IDAgMDAyNS41IDMxLjE1di0xMy43NWEyMC43NSAyMSAwIDAxOC41LTQwLjI1IDIwLjc1IDIxIDAgMDE4LjUgNDAuMjV2MTMuNzVhMzcgMzQuNSAwIDAwMjUuNS0zMS4xNWgxN2EyMiAyMS4xNSAwIDAxLTEwMiAweiIvPgogICAgPHBhdGggZD0iTSAxMDEgNDhhMi43NyAyLjY3IDAgMDAtMTAyIDBoIDE3YTIuOTcgMi44IDAgMDE2OCAweiIvPgo8L3N2Zz4K
[pdsinterop-site]: https://pdsinterop.org/
[project-stage-badge: Development]: https://img.shields.io/badge/Project%20Stage-Development-yellowgreen.svg
[project-stage-page]: https://blog.pother.ca/project-stages/
[standard-readme-link]: https://github.com/RichardLitt/standard-readme
[standard-readme-shield]: https://img.shields.io/badge/-Standard%20Readme-brightgreen.svg
[version-link]: https://packagist.org/packages/pdsinterop/php-solid-server
[version-shield]: https://img.shields.io/github/v/release/pdsinterop/php-solid-server?sort=semver
