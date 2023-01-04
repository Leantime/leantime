TMPDIR := $(shell mktemp -d)
ROOT_DIR := $(shell pwd)
VERSION := $(shell grep "appVersion" ./config/appSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')
.ONESHELL: 
.SHELLFLAGS += -e

install-deps: 
	npm install
	composer install --no-dev --optimize-autoloader

build-js: install-deps
	./node_modules/.bin/grunt Build-All

build: install-deps build-js
	mkdir -p $(TMPDIR)/leantime/release
	mkdir -p target
	cp -R ./* $(TMPDIR)/leantime/release/
	cd $(TMPDIR)/leantime/release
	rm -f vendor/endroid/qr-code/assets/fonts/noto_sans.otf
	rm -rf vendor/deeplcom
	rm -rf src/languages/mltranslate
	rm -rf vendor/mpdf/mpdf/ttfonts
	rm -rf custom/*/*
	rm -rf public/theme/*/css/custom.css
	rm -f -R .git
	rm -f -R .github
	rm -rf *.md
	rm -rf logs/*
	rm -rf phpcs.xml
	rm -rf crowdin.yml
	rm -R node_modules
	rm -R public/images/Screenshots
	rm -f .gitattributes .gitignore composer.json composer.lock gruntfile.js package-lock.json package.json
	rm -f makefile
	find ./src/domain/ -maxdepth 2 -name "js" -exec rm -rf {} \; || true
	find ./public/js/ -mindepth 1 ! -name "*compiled*" -exec rm -f -rf {} \; || true
	mv $(TMPDIR)/leantime/release $(ROOT_DIR)/target/leantime

package:
	cd target
	echo $(VERSION)
	zip -r -X "Leantime-v$(VERSION)$1.zip" leantime
	tar -zcvf "Leantime-v$(VERSION)$1.tar.gz" leantime

clean:
	rm -rf release
	rm -rf target
	rm -rf leantime

.PHONY: install-deps build-js build package clean
