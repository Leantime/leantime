# Makefile - Build and update all files used by Leantine

JS_APP_FILES = public/js/app/app.js \
    public/js/app/core/custom.js \
    public/js/app/core/tableHandling.js \
    public/js/app/core/wysiwyg.js \
    $(wildcard src/domain/*/js/*.js)
JS_BASE_LIB_FILES = node_modules/jquery/dist/jquery.js \
    node_modules/jquery-migrate/dist/jquery-migrate.js \
    node_modules/jquery-ui-dist/jquery-ui.js \
    node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.js \
    node_modules/moment/moment.js \
    node_modules/js-cookie/dist/js.cookie.js \
    public/js/libs/bootstrap.min.js \
    public/js/libs/bootstrap-timepicker.min.js \
    public/js/libs/bootstrap-fileupload.min.js \
    public/js/libs/slimselect.min.js \
    public/js/libs/jquery.jgrowl.js \
    public/js/libs/chosen.jquery.min.js \
    public/js/libs/jquery.form.js \
    public/js/libs/jquery.tagsinput.min.js
# NOT FOUND BUT REFERENCED IN GRUNT: node_modules/jquery.uniform/dist/js/jquery.uniform.standalone.js
JS_EXTENDED_LIB_FILES = node_modules/croppie/croppie.js \
    node_modules/chart.js/dist/chart.js \
    node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.js \
    node_modules/packery/dist/packery.pkgd.js \
    node_modules/imagesloaded/imagesloaded.pkgd.js \
    node_modules/tether-shepherd/dist/js/tether.js \
    node_modules/tether-shepherd/dist/js/shepherd.js \
    node_modules/datatables.net/js/jquery.dataTables.js \
    node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js \
    node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.js \
    node_modules/datatables.net-buttons/js/dataTables.buttons.js \
    node_modules/datatables.net-buttons/js/buttons.html5.js \
    node_modules/datatables.net-buttons/js/buttons.print.js \
    node_modules/datatables.net-buttons/js/buttons.colVis.js \
    node_modules/tinymce/tinymce.js \
    node_modules/tinymce/icons/default/icons.js \
    node_modules/tinymce/jquery.tinymce.js \
    node_modules/tinymce/themes/silver/theme.js \
    node_modules/tinymce/plugins/autolink/plugin.js \
    node_modules/tinymce/plugins/link/plugin.js \
    node_modules/tinymce/plugins/textcolor/plugin.js \
    node_modules/tinymce/plugins/image/plugin.js \
    node_modules/tinymce/plugins/lists/plugin.js \
    node_modules/tinymce/plugins/table/plugin.js \
    node_modules/tinymce/plugins/save/plugin.js \
    node_modules/tinymce/plugins/preview/plugin.js \
    node_modules/tinymce/plugins/media/plugin.js \
    node_modules/tinymce/plugins/searchreplace/plugin.js \
    node_modules/tinymce/plugins/paste/plugin.js \
    node_modules/tinymce/plugins/directionality/plugin.js \
    node_modules/tinymce/plugins/fullscreen/plugin.js \
    node_modules/tinymce/plugins/noneditable/plugin.js \
    node_modules/tinymce/plugins/visualchars/plugin.js \
    node_modules/tinymce/plugins/emoticons/plugin.js \
    node_modules/tinymce/plugins/emoticons/js/emojis.min.js \
    node_modules/tinymce/plugins/template/plugin.js \
    node_modules/tinymce/plugins/advlist/plugin.js \
    node_modules/tinymce/plugins/autoresize/plugin.js \
    node_modules/isotope-layout/dist/isotope.pkgd.js \
    node_modules/jstree/dist/jstree.js \
    node_modules/@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js \
    public/js/libs/tinymce-plugins/helper.js \
    public/js/libs/tinymce-plugins/checklist/index.js \
    public/js/libs/tinymce-plugins/shortlink/index.js \
    public/js/libs/tinymce-plugins/bettertable/index.js \
    public/js/libs/tinymce-plugins/collapsibleheaders/index.js \
    public/js/libs/tinymce-plugins/embed/index.js \
    public/js/libs/fullcalendar.min.js \
    public/js/libs/simple-color-picker-master/jquery.simple-color-picker.js \
    public/js/libs/simpleGantt/moment.min.js \
    public/js/libs/simpleGantt/snap.svg-min.js \
    public/js/libs/simpleGantt/frappe-gantt.min.js \
    public/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js
CSS_FILES = public/less/main.less

JS_MINIFIED = public/js/compiled-app.min.js public/js/jsSourceMapAppSrc.map \
    public/js/compiled-base-libs.min.js public/js/jsSourceMapBaseLib.map \
    public/js/compiled-extended-libs.min.js public/js/jsSourceMapExtendedSrc.map
CSS_MINIFIED = public/css/main.css

LANG_DIR = ./src/language
MLTR_DIR = ./tools/mltranslate

GRUNT_CMD = ./node_modules/grunt/bin/grunt
MLTR_CMD = ./tools/mltranslate/mltranslate.php


# Generic actions
all:	composer npm minify 
	    @/usr/bin/rm -fv logs/error.log

translate-all:	translate notranslate

translate:		mltr-de mltr-es mltr-fr mltr-it mltr-ja mltr-nl mltr-pt-BR mltr-pt-PT mltr-ru mltr-sv mltr-tr mltr-zh-CN

notranslate:	mltr-he mltr-no mltr-pl mltr-zh-ZW mltr-de-CH mltr-fr-CH

clean:
	    @/usr/bin/rm -fv logs/error.log

release:
	    ./createReleasePackage.sh


# Specific actions

## Update composer and npm
composer:
	    composer update

npm:
	    npm update

## Minfy files using Grunt
minify:    $(JS_MINIFIED) $(CSS_MINIFIED)

public/js/compiled-app.min.js public/js/jsSourceMapAppSrc.map:    $(JS_APP_FILES)
	    $(GRUNT_CMD) Build-App-Src

public/js/compiled-base-libs.min.js public/js/jsSourceMapBaseLib.map:    $(JS_BASE_LIB_FILES)
	    $(GRUNT_CMD) Build-Base-Lib

public/js/compiled-extended-libs.min.js public/js/jsSourceMapExtendedSrc.map:    $(JS_EXTENDED_LIB_FILES)
	    $(GRUNT_CMD) Build-Extended-Src

public/css/main.css: $(CSS_FILES)
	    $(GRUNT_CMD) Build-Less-Dev


# - Translate language files
mltr-de:    $(MLTR_DIR)/de-DE.tra

mltr-es:    $(MLTR_DIR)/es-ES.tra

mltr-fr:    $(MLTR_DIR)/fr-FR.tra

mltr-it:    $(MLTR_DIR)/it-IT.tra

mltr-ja:    $(MLTR_DIR)/ja-JA.tra

mltr-nl:    $(MLTR_DIR)/nl-NL.tra

mltr-pt-BR: $(MLTR_DIR)/pt-BR.tra

mltr-pt-PT: $(MLTR_DIR)/pt-PT.tra

mltr-ru:    $(MLTR_DIR)/ru-RU.tra

mltr-sv:    $(MLTR_DIR)/sv-SE.tra

mltr-tr:    $(MLTR_DIR)/tr-TR.tra

mltr-zh-CN: $(MLTR_DIR)/zh-CN.tra

# - Non translatable languages
mltr-he:	$(MLTR_DIR)/he-IL.tra

mltr-no:	$(MLTR_DIR)/no-NO.tra

mltr-pl:	$(MLTR_DIR)/pl-PL.tra

mltr-zh-ZW:	$(MLTR_DIR)/zh-TW.tra

mltr-de-CH:	$(MLTR_DIR)/de-CH.tra

mltr-fr-CH:	$(MLTR_DIR)/fr-CH.tra


$(MLTR_DIR)/de-DE.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/de-DE.ini
	    $(MLTR_CMD) en de $(LANG_DIR)/en-US.ini $(LANG_DIR)/de-DE.ini $(MLTR_DIR)/de-DE.tra

$(MLTR_DIR)/es-ES.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/es-ES.ini
	    $(MLTR_CMD) en es $(LANG_DIR)/en-US.ini $(LANG_DIR)/es-ES.ini $(MLTR_DIR)/es-ES.tra

$(MLTR_DIR)/fr-FR.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/fr-FR.ini
	    $(MLTR_CMD) en fr $(LANG_DIR)/en-US.ini $(LANG_DIR)/fr-FR.ini $(MLTR_DIR)/fr-FR.tra

$(MLTR_DIR)/it-IT.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/it-IT.ini
	    $(MLTR_CMD) en it $(LANG_DIR)/en-US.ini $(LANG_DIR)/it-IT.ini $(MLTR_DIR)/it-IT.tra

$(MLTR_DIR)/ja-JA.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/ja-JA.ini
	    $(MLTR_CMD) en ja $(LANG_DIR)/en-US.ini $(LANG_DIR)/ja-JA.ini $(MLTR_DIR)/ja-JA.tra

$(MLTR_DIR)/nl-NL.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/nl-NL.ini
	    $(MLTR_CMD) en nl $(LANG_DIR)/en-US.ini $(LANG_DIR)/nl-NL.ini $(MLTR_DIR)/nl-NL.tra

$(MLTR_DIR)/pt-BR.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/pt-BR.ini
	    $(MLTR_CMD) en pt-BR $(LANG_DIR)/en-US.ini $(LANG_DIR)/pt-BR.ini $(MLTR_DIR)/pt-BR.tra

$(MLTR_DIR)/pt-PT.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/pt-PT.ini
	    $(MLTR_CMD) en pt-PT $(LANG_DIR)/en-US.ini $(LANG_DIR)/pt-PT.ini $(MLTR_DIR)/pt-PT.tra

$(MLTR_DIR)/ru-RU.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/ru-RU.ini
	    $(MLTR_CMD) en ru $(LANG_DIR)/en-US.ini $(LANG_DIR)/ru-RU.ini $(MLTR_DIR)/ru-RU.tra

$(MLTR_DIR)/tr-TR.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/tr-TR.ini
	    $(MLTR_CMD) en tr $(LANG_DIR)/en-US.ini $(LANG_DIR)/tr-TR.ini $(MLTR_DIR)/tr-TR.tra

$(MLTR_DIR)/sv-SE.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/sv-SE.ini
	    $(MLTR_CMD) en sv $(LANG_DIR)/en-US.ini $(LANG_DIR)/sv-SE.ini $(MLTR_DIR)/sv-SE.tra

$(MLTR_DIR)/zh-CN.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/zh-CN.ini
	    $(MLTR_CMD) en zh $(LANG_DIR)/en-US.ini $(LANG_DIR)/zh-CN.ini $(MLTR_DIR)/zh-CN.tra


$(MLTR_DIR)/he-IL.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/he-IL.ini
	    $(MLTR_CMD) en he $(LANG_DIR)/en-US.ini $(LANG_DIR)/he-IL.ini $(MLTR_DIR)/he-IL.tra

$(MLTR_DIR)/no-NO.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/no-NO.ini
	    $(MLTR_CMD) en no $(LANG_DIR)/en-US.ini $(LANG_DIR)/no-NO.ini $(MLTR_DIR)/no-NO.tra

$(MLTR_DIR)/pl-PL.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/pl-PL.ini
	    $(MLTR_CMD) en pl $(LANG_DIR)/en-US.ini $(LANG_DIR)/pl-PL.ini $(MLTR_DIR)/pl-PL.tra

$(MLTR_DIR)/zh-TW.tra:    $(LANG_DIR)/en-US.ini $(LANG_DIR)/zh-TW.ini
	    $(MLTR_CMD) en zh-TW $(LANG_DIR)/en-US.ini $(LANG_DIR)/zh-TW.ini $(MLTR_DIR)/zh-TW.tra

$(MLTR_DIR)/de-CH.tra:    $(LANG_DIR)/de-DE.ini $(LANG_DIR)/de-CH.ini
	    $(MLTR_CMD) de de-CH $(LANG_DIR)/de-DE.ini $(LANG_DIR)/de-CH.ini $(MLTR_DIR)/de-CH.tra

$(MLTR_DIR)/fr-CH.tra:    $(LANG_DIR)/fr-FR.ini $(LANG_DIR)/fr-CH.ini
	    $(MLTR_CMD) fr fr-CH $(LANG_DIR)/fr-FR.ini $(LANG_DIR)/fr-CH.ini $(MLTR_DIR)/fr-CH.tra


