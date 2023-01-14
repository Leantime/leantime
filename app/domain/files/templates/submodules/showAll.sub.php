<?php
/** @var leantime\services\auth $login */
/** @var leantime\core\language $language */
$module = \leantime\core\frontcontroller::getModuleName('');
$moduleId = $_GET['id'] ?? '';
?>
<div id="fileManager">

    <?php echo $this->displayNotification() ?>

    <div class="uploadWrapper">

        <a href="javascript:void(0);" id="cancelLink" class="btn btn-default" style="display:none;">Cancel</a>
        <div class="extra" style="margin-top:5px;"></div>
        <div style="">
            <div class="file-upload-input" style="margin:auto;  display:inline-block"></div>
            <div class="dropdownWrapper" style="display:inline-block;">
                <button class="btn dropdown-toggle" data-toggle="dropdown">Record</button>
                <ul class="dropdown-menu" style="left:0; right:auto; text-align:left;">
                    <li><a href="javascript:void(0);" id="webcamClick"> Webcam</a></li>
                    <li><a href="javascript:void(0);" id="screencaptureLink"">Screen Capture</a></li>
                </ul>
            </div>

        </div>

        <!-- Progress bar #1 -->
        <div class="input-progress"></div>
        <hr />

        <form id="upload-form"></form>

    </div>

    <div class='mediamgr'>

        <div class="mediamgr_content">

            <ul id='medialist' class='listfile'>
                <?php foreach ($this->get('files') as $file) {?>
                    <li class="<?php echo $file['moduleId'] ?>">
                        <div class="inlineDropDownContainer dropright" style="float:right;">

                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="nav-header"><?php echo $this->__("subtitles.file"); ?></li>
                                <li><a target="_blank" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $this->__("links.download"); ?></a></li>

                                <?php

                                if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <li><a href="<?=BASE_URL ?>/files/showAll?delFile=<?php echo $file['id'] ?>" class="delete deleteFile"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete"); ?></a></li>
                                <?php  } ?>

                            </ul>
                        </div>
                        <a class="imageLink" href="<?=BASE_URL?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>">
                            <?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))) :  ?>
                                <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>" alt="" />
                            <?php else : ?>
                                <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/images/thumbs/doc.png' />
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

    const uppy = new Uppy.Uppy({ debug: false, autoProceed: true });


    uppy.use(Uppy.DropTarget, { target: '#fileManager' });

    uppy.use(Uppy.FileInput, { target: '.file-upload-input', pretty: true })
    uppy.use(Uppy.XHRUpload, {
        endpoint: '<?=BASE_URL ?>/api/files?module=<?=$module?>&moduleId=<?=$moduleId?>',
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
    // Upload

    uppy.on('upload-success', (file, response) => {
        window.location.reload();
        // do something with file and response
    });



    jQuery("#webcamClick").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.Webcam, { target: '.extra' });
        jQuery("#cancelLink").show();
    });

    jQuery("#screencaptureLink").click(function(){
        jQuery(".uploadWrapper .extra").css("display", "flex");
        uppy.use(Uppy.ScreenCapture, { target: '.extra' });
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

</script>

