#!/bin/sh

mkdir release
cd release

git clone  https://github.com/Leantime/leantime.git

cd leantime

npm install

composer install --no-dev

./node_modules/.bin/grunt Build-All

rm -f -R .git
rm -f -R .github
rm -R node_modules
rm -R public/images/Screenshots
rm .gitattributes .gitignore composer.json composer.lock gruntfile.js package-lock.json package.json

cd ..

zip -r -X Leantime-v$1.zip leantime/*

tar -zcvf Leantime-v$1.tar.gz leantime

rm -R leantime



