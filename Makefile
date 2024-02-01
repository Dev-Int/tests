# —— Inspired by ———————————————————————————————————————————————————————————————
# https://www.strangebuzz.com/fr/snippets/le-makefile-parfait-pour-symfony
# who was inspired by
# http://fabien.potencier.org/symfony4-best-practices.html
# https://speakerdeck.com/mykiwi/outils-pour-ameliorer-la-vie-des-developpeurs-symfony?slide=47
# https://blog.theodo.fr/2018/05/why-you-need-a-makefile-on-your-project/


# Setup ————————————————————————————————————————————————————————————————————————
# Outside docker
EXEC_PHP      = php
GIT           = git
GIT_AUTHOR    = Dev-Int
SYMFONY       = $(EXEC_PHP) bin/console
SYMFONY_BIN   = symfony
COMPOSER      = composer

# Inside docker
DOCKER_COMP   = docker compose
PHP_CONT      = $(DOCKER_COMP) exec php

.DEFAULT_GOAL = help
.PHONY        : help build init up down logs sh vendor composer

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

wait: ## Sleep 5 seconds
	sleep 5


## —— Composer —————————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: composer.lock ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer



## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

fix-perms: ## Fix permissions of all var files
	chmod -R 777 var/*

assets: purge ## Install the assets with symlinks in the public folder
	$(SYMFONY) assets:install public/ --symlink --relative

purge: ## Purge cache and logs
	rm -rf var/cache/* var/logs/*


## —— Symfony binary 🎵 —————————————————————————————————————————————————————————
bin-install: ## Download and install the binary in the project (file is ignored)
	curl -sS https://get.symfony.com/cli/installer | bash
	mv ~/.symfony/bin/symfony .

cert-install: ## Install the local HTTPS certificates
	$(SYMFONY_BIN) server:ca:install

serve: ## Serve the application with HTTPS support
	$(SYMFONY_BIN) serve --daemon --port=8000

unserve: ## Stop the webserver
	$(SYMFONY_BIN) server:stop


## —— Project ——————————————————————————————————————————————————————————————————
reload: load-fixtures ## Reload fixtures

load-fixtures: ## Build the DB, control the schema validity, load fixtures and check the migration status
	$(SYMFONY) doctrine:cache:clear-metadata
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:drop --force
	$(SYMFONY) doctrine:schema:create
	$(SYMFONY) doctrine:schema:validate
	$(SYMFONY) doctrine:fixtures:load -n


## —— Tests ————————————————————————————————————————————————————————————————————
tu: phpunit.xml ## Launch unit tests
	php bin/phpunit --group=unitTest --stop-on-failure


tf: phpunit.xml ## Launch functional tests implying external resources (API, services...)
	php bin/console c:c -e test
	php bin/phpunit --group=functionalTest --stop-on-failure

ta: phpunit.xml ## Launch functional and unit tests
	php bin/phpunit --stop-on-failure


## —— Coding standards ✨ ——————————————————————————————————————————————————————
qa: phpcs stan cs-fixer # lint ## Launch all static analysis tools
	#$(SYMFONY) lint:yaml config
	bin/deptrac analyse --config-file=deptrac.yaml

phpcs: ## Run php_codesniffer
	php ./vendor/bin/phpcs --standard=phpcs.xml -n -p src/

cs-fixer: ## Run php-cs-fixer
	bin/php-cs-fixer fix --diff --verbose

stan: ## Run PHPStan only
	./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 1G

psalm: ## Run psalm only
	./vendor/bin/psalm --show-info=false

init-psalm: ## Init a new psalm config file for a given level, it must be decremented to have stricter rules
	rm ./psalm.xml
	./vendor/bin/psalm --init src/ 3


## —— Deploy & Prod ————————————————————————————————————————————————————————————
#deploy: ## Full no-downtime deployment with EasyDeploy
#	$(SYMFONY) deploy -v
#
#env-check: ## Check the main ENV variables of the project
#	printenv | grep -i app_
#
#le-renew: ## Renew Let's Encrypt HTTPS certificates
#	certbot --apache -d strangebuzz.com -d www.strangebuzz.com


## —— Yarn / JavaScript ————————————————————————————————————————————————————————
client-dev: ## Rebuild assets for the dev env
	yarn install
	yarn run encore dev

client-watch: ## Watch files and build assets when needed for the dev env
	yarn run encore dev --watch

client-build: ## Build assets for production
	yarn run encore production

client-lint: ## Lints Js files
	npx eslint assets/js --fix


## —— Docker 🐳 ———————————————————————————————————————————————————————————————————
init: dist_file build up ## Initialize the project

dist_file: .php-cs-fixer.php.dist phpcs.xml.dist phpunit.xml.dist # copy the .dist files
	cp ./.php-cs-fixer.php.dist ./.php-cs-fixer.php
	cp ./phpcs.xml.dist ./phpcs.xml
	cp ./phpunit.xml.dist ./phpunit.xml
build: compose.yaml ## build docker images
	@$(DOCKER_COMP) build --pull --no-cache
up: ## Start the containers
	@$(DOCKER_COMP) up --detach
down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans
logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow
sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) bash


## —— Stats ————————————————————————————————————————————————————————————————————
stats: ## Commits by the hour for the main author of this project
	$(GIT) log --author="$(GIT_AUTHOR)" --date=iso | perl -nalE 'if (/^Date:\s+[\d-]{10}\s(\d{2})/) { say $$1+0 }' | sort | uniq -c|perl -MList::Util=max -nalE '$$h{$$F[1]} = $$F[0]; }{ $$m = max values %h; foreach (0..23) { $$h{$$_} = 0 if not exists $$h{$$_} } foreach (sort {$$a <=> $$b } keys %h) { say sprintf "%02d - %4d %s", $$_, $$h{$$_}, "*"x ($$h{$$_} / $$m * 50); }'
