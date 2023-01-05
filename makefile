VERSION := $(shell grep "appVersion" ./config/appSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')
TARGET_DIR:= ./target/leantime
install-deps: 
	npm install
	composer install --no-dev --optimize-autoloader

build-js: install-deps
	./node_modules/.bin/grunt Build-All

build: install-deps build-js
	mkdir -p $(TARGET_DIR)
	cp -R ./app $(TARGET_DIR)
	cp -R ./bin $(TARGET_DIR)
	cp -R ./config $(TARGET_DIR)
	cp -R ./logs $(TARGET_DIR)
	cp -R ./public $(TARGET_DIR)
	cp -R ./userfiles $(TARGET_DIR)
	cp -R ./vendor $(TARGET_DIR)
	cp  ./.htaccess $(TARGET_DIR)
	cp  ./LICENSE $(TARGET_DIR)
	cp  ./nginx*.conf $(TARGET_DIR)
	cp  ./updateLeantime.sh $(TARGET_DIR)

package:
	cd target && zip -r -X "Leantime-v$(VERSION)$$1.zip" leantime
	cd target && tar -zcvf "Leantime-v$(VERSION)$$1.tar.gz" leantime

clean:
	rm -rf $(TARGET_DIR)

.PHONY: install-deps build-js build package clean
