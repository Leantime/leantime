VERSION := $(shell grep "appVersion" ./app/Core/Configuration/AppSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')
TARGET_DIR:= ./target/leantime
DESC:=$(shell git log -1 --pretty=%B)

DOCS_DIR:= ./builddocs
DOCS_REPO:= git@github.com:Leantime/docs.git
RUNNING_DOCKER_CONTAINERS:= $(shell docker ps -a -q)
RUNNING_DOCKER_VOLUMES:= $(shell docker volume ls -q)

install-deps-dev:
	npm install
	composer install

install-deps:
	npm install
	composer install --no-dev --optimize-autoloader

build: install-deps clear-cache
	npx mix --production
	node generateBlocklist.mjs

build-dev: install-deps-dev clear-cache
	npx mix
	node generateBlocklist.mjs

package: clean build
	mkdir -p $(TARGET_DIR)

	#copy code files
	cp -R ./app $(TARGET_DIR)
	cp -R ./config $(TARGET_DIR)
	cp -R ./bin $(TARGET_DIR)
	cp -R ./bootstrap $(TARGET_DIR)
	cp -R ./public $(TARGET_DIR)
	cp -R ./vendor $(TARGET_DIR)

	#create empty cache and storage folders
	mkdir -p $(TARGET_DIR)/storage
	mkdir -p $(TARGET_DIR)/storage/framework
	mkdir -p $(TARGET_DIR)/storage/framework/cache
	mkdir -p $(TARGET_DIR)/storage/framework/sessions
	mkdir -p $(TARGET_DIR)/storage/framework/views

	#prepare log file
	mkdir -p $(TARGET_DIR)/storage/logs
	touch $(TARGET_DIR)/storage/logs/leantime.log

	mkdir -p $(TARGET_DIR)/userfiles
	touch   $(TARGET_DIR)/userfiles/.gitkeep


	rm -rf $(TARGET_DIR)/config/.env
	rm -rf $(TARGET_DIR)/public/theme/*/css/custom.css

	# Remove user files
	rm -rf $(TARGET_DIR)/app/Plugins/*

	# Remove user files
	rm -rf $(TARGET_DIR)/userfiles/*
	rm -rf $(TARGET_DIR)/public/userfiles/*

	# Removing unneeded items for release
	rm -rf $(TARGET_DIR)/public/dist/images/Screenshots

	# Removing javascript directories
	find  $(TARGET_DIR)/app/Domain/ -depth -maxdepth 2 -name "js" -exec rm -rf {} \;

	# Removing un-compiled javascript files
	find $(TARGET_DIR)/public/dist/js/ -depth -mindepth 1 ! -name "*compiled*" -exec rm -rf {} \;

	#create zip files
	cd target/leantime && zip -r -X ../"Leantime-v$(VERSION)$$1.zip" .
	cd target/leantime && tar -zcvf ../"Leantime-v$(VERSION)$$1.tar.gz" .

gendocs: # Requires github CLI (brew install gh)
	# Delete the temporary docs directory if exists
	rm -rf $(DOCS_DIR)

	# Make a temporary directory for docs
	mkdir -p $(DOCS_DIR)

	# Clone the docs
	git clone $(DOCS_REPO) $(DOCS_DIR)

	# Generate the docs
	phpDocumentor --config=phpdoc.xml
	phpDocumentor --config=phpdoc-api.xml

	php vendor/bin/leantime-documentor parse app --format=markdown --template=templates/markdown.php --output=builddocs/technical/hooks.md --memory-limit=-1

pushdocs:
	# create pull request
	cd $(DOCS_DIR) && git switch -c "release/$(VERSION)"
	cd $(DOCS_DIR) && git add -A
	cd $(DOCS_DIR) && git commit -m "Generated docs release $(VERSION)"
	cd $(DOCS_DIR) && git push --set-upstream origin "release/$(VERSION)"
	cd $(DOCS_DIR) && gh pr create --title "release/$(VERSION) --body "

	# Delete the temporary docs directory
	rm -rf $(DOCS_DIR)

clean:
	rm -rf $(TARGET_DIR)

run-dev: build-dev
	docker compose --file .dev/docker-compose.yaml up --detach --build --remove-orphans

acceptance-test: build-dev
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml up --detach --build --remove-orphans
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run Acceptance -vvv

unit-test: build-dev
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml up --detach --build --remove-orphans
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept build
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run Unit -vv

api-test: build-dev
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml up --detach --build --remove-orphans
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept build
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run Api -vv

acceptance-test-ci: build-dev
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml up --detach --build --remove-orphans
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept build
	docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run Acceptance --steps

codesniffer:
	./vendor/squizlabs/php_codesniffer/bin/phpcs app -d memory_limit=1048M

codesniffer-fix:
	./vendor/squizlabs/php_codesniffer/bin/phpcbf app -d memory_limit=1048M

get-version:
	@echo $(VERSION)

phpstan:
	./vendor/bin/phpstan analyse -c .phpstan/phpstan.neon --memory-limit 2G

update-carbon-macros:
	./vendor/bin/carbon macro Leantime\\Core\\Support\\CarbonMacros app/Core/Support/CarbonMacros.php

test-code-style:
	./vendor/bin/pint --test --config .pint/pint.json

fix-code-style:
	./vendor/bin/pint --config .pint/pint.json

clear-cache:
	rm -rf ./bootstrap/cache/*.php
	rm -rf ./storage/framework/composerPaths.php
	rm -rf ./storage/framework/viewPaths.php
	rm -rf ./storage/framework/cache/*.php
	rm -rf ./storage/framework/views/*.php

.PHONY: install-deps build-js build package clean run-dev
