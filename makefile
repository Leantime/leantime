VERSION := $(shell grep "appVersion" ./config/appSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')

install-deps: 
	npm install
	composer install --no-dev --optimize-autoloader

build-js: install-deps
	./node_modules/.bin/grunt Build-All

build: install-deps build-js
	mkdir -p /target/leantime
	cp -R ./app ./target/leantime
	cp -R ./bin ./target/leantime
	cp -R ./config ./target/leantime
	cp -R ./logs ./target/leantime
	cp -R ./public ./target/leantime
	cp -R ./userfiles ./target/leantime
	cp -R ./vendor ./target/leantime
	cp  ./.htaccess ./target/leantime
	cp  ./LICENSE ./target/leantime
	cp  ./nginx*.conf ./target/leantime
	cp  ./updateLeantime.sh ./target/leantime

package:
	cd target && zip -r -X "Leantime-v$(VERSION)$$1.zip" leantime
	cd target && tar -zcvf "Leantime-v$(VERSION)$$1.tar.gz" leantime

clean:
	rm -rf $(WORKDIR)
	rm -rf release
	rm -rf target
	rm -rf leantime

.PHONY: install-deps build-js build package clean
