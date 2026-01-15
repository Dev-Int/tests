Tests
=====

![Tests](https://github.com/Dev-Int/tests/workflows/Tests/badge.svg) 
![Test Coverage](https://raw.githubusercontent.com/Dev-Int/tests/output/coverage.svg) 
[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/Dev-Int/tests/blob/master/LICENSE)

This repository comes from ideas coming from [GLSR](https://github.com/Dev-Int/glsr), where, in 2020, I found myself 
 stuck on dependency updates, after several months of abandonment. At the same time, a friend of mine needed help with
 unit testing.
 
So I started with unit tests, then, along the way, I resumed the desire to develop my project. This one starts from
 scratch, only the entities will not change, to begin with. The domain is there. 😉

To train and create skills, I want to develop according to the DDD vision, and the different testing approaches
 (ATDD, TDD, BDD ...)
 
### Features

To start in the right direction, I start by preparing my [use cases](https://github.com/Dev-Int/tests/labels/use%20case),
 and adding the resulting tests after this list:

- [x] rewrite Domain model to check and correct my previous choices

- [ ] install [Behat](https://docs.behat.org/en/latest/quick_start.html)

## Installation

clone the repo at first
```
git clone https://github.com/Dev-Int/tests.git
```
And then install dependencies

1. with docker
```shell
make init
```
2. with php installed
```shell
composer install
```

## Use

After init, you can start the project with this command:
```shell
make start
```
To know all you can do with the Makefile, just run:
```shell
make
```

## Developer Tools

### grepai - Semantic Code Search

[grepai](https://github.com/yoanbernabeu/grepai) enables semantic code search for AI agents, reducing token consumption by ~50%.

#### Installation

1. **Install grepai**
```bash
curl -sSL https://raw.githubusercontent.com/yoanbernabeu/grepai/main/install.sh | sh
```

2. **Install Ollama** (for 100% local execution)
```bash
curl -fsSL https://ollama.com/install.sh | sh
ollama pull nomic-embed-text
```

3. **Initialize in project**
```bash
grepai init
grepai watch  # Start background indexing
```

#### Usage

```bash
# Semantic search
grepai search "authentication error handling" --json --compact

# Call tracing
grepai trace callers "UserRepository" --json
grepai trace callees "CreateUserHandler" --json
```

For full documentation, see the [grepai docs](https://yoanbernabeu.github.io/grepai/).

## Licence

[MIT](https://choosealicense.com/licenses/mit/)

## All docs to use docker installation

1. [Build options](docs/docker/build.md)
2. [Using Symfony Docker with an existing project](docs/docker/existing-project.md)
3. [Support for extra services](docs/docker/extra-services.md)
4. [Deploying in production](docs/docker/production.md)
5. [Debugging with Xdebug](docs/docker/xdebug.md)
6. [TLS Certificates](docs/docker/tls.md)
7. [Using a Makefile](docs/docker/makefile.md)
8. [Troubleshooting](docs/docker/troubleshooting.md)

## Docs for the project

[The doc](docs/index.md)
