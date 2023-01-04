TMPDIR := /tmp
ROOT_DIR := $(shell pwd)
VERSION := $(shell grep "appVersion" ./config/appSettings.php |awk -F' = ' '{print substr($$2,2,length($$2)-3)}')
WORKDIR := $(TMPDIR)/leantime/release

install-deps: 
	npm install
	composer install --no-dev --optimize-autoloader

build-js: install-deps
	./node_modules/.bin/grunt Build-All

build: install-deps build-js
	mkdir -p $(WORKDIR)
	mkdir -p target
	cp -R ./* $(WORKDIR)
	rm -f $(WORKDIR)/vendor/endroid/qr-code/assets/fonts/noto_sans.otf
	rm -rf $(WORKDIR)/vendor/deeplcom
	rm -rf $(WORKDIR)/src/languages/mltranslate
	rm -rf $(WORKDIR)/vendor/mpdf/mpdf/ttfonts
	rm -rf $(WORKDIR)/custom/*/*
	rm -rf $(WORKDIR)/public/theme/*/css/custom.css
	rm -rf $(WORKDIR)/.git
	rm -rf $(WORKDIR)/.github
	rm -rf $(WORKDIR)/*.md
	rm -rf $(WORKDIR)/logs/*
	rm -rf $(WORKDIR)/phpcs.xml
	rm -rf $(WORKDIR)/crowdin.yml
	rm -rf $(WORKDIR)/node_modules
	rm -rf $(WORKDIR)/public/images/Screenshots
	rm -rf $(WORKDIR)/.gitattributes 
	rm -rf $(WORKDIR)/.gitignore 
	rm -rf $(WORKDIR)/composer.json 
	rm -rf $(WORKDIR)/composer.lock 
	rm -rf $(WORKDIR)/gruntfile.js 
	rm -rf $(WORKDIR)/package-lock.json 
	rm -rf $(WORKDIR)/package.json
	rm -rf $(WORKDIR)/makefile
	find $(WORKDIR)/src/domain/ -maxdepth 2 -name "js" -exec rm -rf {} \; || true
	find $(WORKDIR)/public/js/ -mindepth 1 ! -name "*compiled*" -exec rm -rf {} \; || true
	mv $(WORKDIR) $(ROOT_DIR)/target/leantime

package:
	cd target && zip -r -X "Leantime-v$(VERSION)$$1.zip" leantime
	cd target && tar -zcvf "Leantime-v$(VERSION)$$1.tar.gz" leantime

clean:
	rm -rf $(WORKDIR)
	rm -rf release
	rm -rf target
	rm -rf leantime

.PHONY: install-deps build-js build package clean
