#!/bin/sh

# Creating folder for release build
mkdir -p release
cd release

#Pulling latest master from Github
rm -R -f leantime
git clone https://github.com/Leantime/leantime.git
cd leantime

#Pulling dependencies in
npm install

composer install --no-dev --optimize-autoloader


#Building dependencies
./node_modules/.bin/grunt Build-All

#Remove font for QR code generator (not needed if no label is used)
rm -f vendor/endroid/qr-code/assets/fonts/noto_sans.otf

#Removing unneeded items for release
rm -f -R .git
rm -f -R .github
rm -R node_modules
rm -R public/images/Screenshots
rm .gitattributes .gitignore composer.json composer.lock gruntfile.js package-lock.json package.json
rm createReleasePackage.sh

#removing js directories
find ./src/domain/ -maxdepth 2 -name "js" -exec rm -r {} \;

#removing uncompiled js files
find ./public/js/ -mindepth 1 ! -name "*compiled*" -exec rm -f -r {} \;

#Exiting release folder and creating archives for Github
cd ..
version=`grep "appVersion" leantime/config/appSettings.php |awk -F' = ' '{print substr($2,2,length($2)-3)}'`
zip -r -X "Leantime-v$version$1.zip" leantime
tar -zcvf "Leantime-v$version$1.tar.gz" leantime

#Removing 
rm -R leantime
