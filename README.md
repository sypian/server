[![Build Status](https://travis-ci.org/sypian/server.svg?branch=master)](https://travis-ci.org/sypian/server) [![Test Coverage](https://api.codeclimate.com/v1/badges/db835095d9524974f421/test_coverage)](https://codeclimate.com/github/sypian/server/test_coverage) [![Maintainability](https://api.codeclimate.com/v1/badges/db835095d9524974f421/maintainability)](https://codeclimate.com/github/sypian/server/maintainability)

# sypian server

The sypian server delivering an API.

It serves as an api to save projects, categories and their relations into a neo4j database.

The API specification is defined in the [swagger.yml](swagger.yml).

## Why that naming?

Why sypian? wtf? Yeah, it means "see your projects in a network".

# Development

## Setup development environment

Load vendor libraries through composer and build the docker image.

```bash
make build
```

## Run tests

First start the neo4j server which is needed for the integration tests.

```bash
make neo4j
```

Then run the test command.

```bash
make test
```
## Run the linter

```bash
make lint
```

# Deployment

## Container overview

- php: Holds the php binary and the source code of sypian.
- nginx: Gets the source code as a volume and serves as the webserver, connected to the php container.
- neo4j: The neo4j database connected to the php container.

## Make and run deployable components

We use an environment for building the production docker image.

```bash
export SYPIAN_BUILD_ENV=production
docker run --rm --volume ${CODE_DIR}:/app composer install --ignore-platform-reqs
docker-compose build
```

Now you can run the containers.

```bash
docker-compose run
```
