<?php

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Fileupload;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

$module = "project";
$action = Frontcontroller::getActionName('');
$maxSize = Fileupload::getMaximumFileUploadSize();
$moduleId = session("currentProject");
?>
<div class="pageheader">

    <div class="pageicon"><span class="fa fa-fw fa-file"></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectName")); ?></h5>
        <h1><?=$tpl->__("headlines.files"); ?></h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">


    <div id="fileManager">
        <div class="maincontentinner">
            <?php
            echo $tpl->displayNotification();
            ?>
            <h5 class="subtitle"><?=$tpl->__("headline.browse_files_headline"); ?></h5>




            <?php echo $tpl->displayNotification() ?>

        <?php if ($login::userIsAtLeast($roles::$editor)) {?>
        <div class="uploadWrapper">

            <a href="javascript:void(0);" id="cancelLink" class="btn btn-default" style="display:none;"><?php echo $tpl->__("links.cancel"); ?></a>
            <div class="extra" style="margin-top:5px;"></div>
            <div class="fileUploadDrop">
                <p><i><?=$tpl->__("text.drop_files"); ?></i></p>
                <div class="file-upload-input" style="margin:auto;  display:inline-block"></div>
                <a href="javascript:void(0);" id="webcamClick"><?=$tpl->__("label.webcam"); ?></a>
                <a href="javascript:void(0);" id="screencaptureLink"><?=$tpl->__("label.screen_recording"); ?></a>
            </div>

            <!-- Progress bar #1 -->
            <div class="input-progress"></div>

            <div class="input-error"></div>

            <form id="upload-form"></form>

        </div>
        <?php } ?>
        </div>
        <div class="maincontentinner">

        <div class='mediamgr'>

            <div class="mediamgr_content">

                <ul id='medialist' class='listfile'>
                    <?php foreach ($tpl->get('files') as $file) {?>
                        <li class="file-module-<?php echo $file['moduleId'] ?>">
                            <div class="inlineDropDownContainer dropright" style="float:right;">

                                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="nav-header"><?php echo $tpl->__("subtitles.file"); ?></li>
                                    <li><a target="_blank" href="<?=BASE_URL ?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $tpl->__("links.download"); ?></a></li>

                                    <?php

                                    if ($login::userIsAtLeast($roles::$editor)) { ?>
                                        <li><a href="<?=BASE_URL ?>/files/showAll?delFile=<?php echo $file['id'] ?>" class="delete deleteFile"><i class="fa fa-trash"></i> <?php echo $tpl->__("links.delete"); ?></a></li>
                                    <?php  } ?>

                                </ul>
                            </div>
                            <a class="imageLink" data-ext="<?php echo $file['extension'] ?>" href="<?=BASE_URL?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>">
                                <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) :  ?>
                                    <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>" alt="" />
                                <?php else : ?>
                                    <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/dist/images/doc.png' />
                                <?php endif; ?>
                                <span class="filename"><?php echo substr($file['realName'], 0, 10) . "(...)." . $file['extension'] ?></span>
                            </a>
                        </li>
                    <?php } ?>

                <br class="clearall" />
                </ul>

                <br class="clearall" />

            </div><!--mediamgr_content-->

            <br class="clearall" />
        </div><!--mediamgr-->
    </div>

    </div>
</div>

<script type='text/javascript'>
    jQuery(document).ready(function(){

        jQuery('#widgetAction').click(function(){
            jQuery('.widgetList').toggle();
        });
        jQuery('#widgetAction2').click(function(){
            jQuery('.widgetList2').toggle();
        });
    });
</script>


    <script type="text/javascript">
        jQuery(document).ready(function(){

            let modalTypes = ["jpg", "jpeg", "png", "gif", "apng", "webp", "avif"];
            jQuery(".imageLink").each(function(i) {
                let ext = jQuery(this).attr("data-ext");
                if(modalTypes.includes(ext)) {
                    jQuery(this).nyroModal();
                }
            });

            //Replaces data-rel attribute to rel.
            //We use data-rel because of w3c validation issue
            jQuery('a[data-rel]').each(function() {
                jQuery(this).attr('rel', jQuery(this).data('rel'));
            });

            //jQuery("#medialist a").colorbox();

            <?php if (isset($_GET['modalPopUp'])) { ?>
                jQuery('#medialist a.imageLink').on("click", function(event){

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    var url = jQuery(this).attr("href");

                    //File picker upload callback from tinymce
                    window.filePickerCallback(url, {text: "file"});

                    jQuery.nmTop().close();
                });

            <?php } ?>

            // Media Filter
            jQuery('#mediafilter a').on("click", function(){

                var filter = (jQuery(this).attr('href') != 'all')? '.'+jQuery(this).attr('href') : '*';
                jQuery('#medialist').isotope({ filter: filter });

                jQuery('#mediafilter li').removeClass('current');
                jQuery(this).parent().addClass('current');

                return false;
            });

            jQuery(".deleteFile").nyroModal();


        });
    </script>


<script>

    if (typeof uppy === 'undefined') {


    const uppy = new Uppy.Uppy({
            debug: false,
            autoProceed: true,
            restrictions: {
                maxFileSize: <?=$maxSize?>
            }
    });

    uppy.use(Uppy.DropTarget, { target: '#fileManager' });

    uppy.use(Uppy.FileInput, {
            target: '.file-upload-input',
        pretty: true,
        locale: {
                strings: {
                    chooseFiles: ' Browse',
                }
        }
    });
    uppy.use(Uppy.XHRUpload, {
        endpoint: '<?=BASE_URL ?>/api/files?module=project&moduleId=<?=$moduleId?>',
        formData: true,
        fieldName: 'file',
    });

    uppy.use(Uppy.StatusBar, {
        target: '.input-progress',
        hideUploadButton: true,
        hideAfterFinish: false,
    });

    //uppy.use(Uppy.Webcam, { target: '.extra' });
    //uppy.use(Uppy.ProgressBar, { target: '.input-progress', hideAfterFinish: true });

    //uppy.use(Uppy.Audio, { target: '.extra', showRecordingLength: true });
    //uppy.use(Uppy.ScreenCapture, { target: '.extra' });

    uppy.use(Uppy.Form, { target: '#upload-form' });
    //uppy.use(Uppy.ImageEditor, { target: '.extra' });
    // Allow dropping files on any element or the whole document
    // Optimize images
    uppy.use(Uppy.Compressor);

    /*
    uppy.use(Uppy.ThumbnailGenerator, {
        id: 'ThumbnailGenerator',
        thumbnailWidth: 200,
        thumbnailHeight: 200,
        thumbnailType: 'image/jpeg',
        waitForThumbnailsBeforeUpload: false,
    });

    uppy.on('thumbnail:generated', (file, preview) => {
        const img = document.createElement('img')
        img.src = preview;
        img.width = 100;
        document.body.appendChild(img);

    });*/

    // Upload
    uppy.on("restriction-failed", (file, error) => {

        jQuery(".input-error").html("<span class='label-important'>"+error+"</span>");
        return false
    });

    uppy.on('upload-success', (file, response) => {

        jQuery(".input-error").text('');

        response = response.body;

        if(response.hasOwnProperty("moduleId")){
        /*
        //window.location.hash = "files";
        //window.location.reload();*/

            let html = '<li class="file-module-'+response.moduleId+'">' +
                            '<div class="inlineDropDownContainer dropright" style="float:right;">' +
                                '<a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">' +
                                    '<i class="fa fa-ellipsis-v" aria-hidden="true"></i>' +
                                '</a>' +
                                '<ul class="dropdown-menu">' +
                                    '<li class="nav-header"><?php echo $tpl->__("subtitles.file"); ?></li>' +
                                    '<li><a target="_blank" href="<?=BASE_URL ?>/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'"><?php echo str_replace("'", '"', $tpl->__("links.download")); ?></a></li>'+
                                    <?php
                                    if ($login::userIsAtLeast($roles::$editor)) { ?>
                                        '<li><a href="<?=BASE_URL ?>/files/showAll?delFile='+ response.fileId +'" class="delete deleteFile"><i class="fa fa-trash"></i> <?php echo str_replace("'", '"', $tpl->__("links.delete")); ?></a></li>'+
                                    <?php  } ?>
                                '</ul>'+
                            '</div>'+
                            '<a class="imageLink" href="<?=BASE_URL?>/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'">'+
                                '<img style="max-height: 50px; max-width: 70px;" src="<?=BASE_URL ?>/files/get?module='+ response.module +'&encName='+ response.encName +'&ext='+ response.extension +'&realName='+ response.realName +'" alt="" />'+

                                '<span class="filename">'+response.realName+'.</span>'+
                            '</a>'+
                        '</li>';

                 jQuery("#medialist").append(html);
        }

    });

    jQuery("#webcamClick").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.Webcam, { target: '.extra' });
        jQuery("#cancelLink").show();
    });

    jQuery("#screencaptureLink").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.ScreenCapture,
            {
                displayMediaConstraints: {
                    video: {
                        width: 1280,
                        height: 720,
                        frameRate: {
                            ideal: 3,
                            max: 5,
                        },
                        cursor: 'motion',
                        displaySurface: 'window',
                    },
                },
                target: '.extra'
            });
        jQuery("#cancelLink").show();
    });



    jQuery("#cancelLink").click(function(){
        const instance = uppy.getPlugin('Webcam');
        if(instance) {
            uppy.removePlugin(instance);
        }

        const instance2 = uppy.getPlugin('ScreenCapture');
        if(instance2) {
            uppy.removePlugin(instance2);
        }

        jQuery("#cancelLink").hide();

        jQuery(".uploadWrapper .extra").css("display", "none");


    });

    }

</script>

