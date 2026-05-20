<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
?>
<div class="mediamgr_category">

    <form action='<?= BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id; ?>#files' method='POST' enctype="multipart/form-data" class="formModal">
        <div class="par f-left" style="margin-right: 15px;">
            <input type="hidden" name="upload" value="1" />
            <div class="tw-mb-s" style="margin-bottom:8px;">
                <label style="margin-right:15px; font-weight:normal; cursor:pointer;">
                    <input type="radio" name="uploadType" value="file" class="attachmentUploadTypeToggle" checked />
                    <?php echo $tpl->__('label.upload_file'); ?>
                </label>
                <label style="font-weight:normal; cursor:pointer;">
                    <input type="radio" name="uploadType" value="link" class="attachmentUploadTypeToggle" />
                    <?php echo $tpl->__('label.attach_link'); ?>
                </label>
            </div>

            <div class="attachmentFileInput">
                <div class='fileupload fileupload-new' data-provides='fileupload'>
                    <input type="hidden" />
                    <div class="input-append">
                        <div class="uneditable-input span3">
                            <i class="fa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                        </div>
                        <span class="btn btn-file">
                            <span class="fileupload-new"><?php echo $tpl->__('buttons.select_file'); ?></span>
                            <span class='fileupload-exists'><?php echo $tpl->__('buttons.change'); ?></span>
                            <input type='file' name='file' />
                        </span>
                        <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'><?php echo $tpl->__('buttons.remove'); ?></a>
                    </div>
                </div>
            </div>

            <div class="attachmentLinkInput" style="display:none;">
                <input type="url" name="linkUrl" placeholder="https://example.com/document" style="width:280px;" />
                <input type="text" name="linkName" placeholder="<?php echo $tpl->__('label.link_name_optional'); ?>" style="width:280px; margin-top:5px;" />
            </div>

            <script>
                jQuery(function() {
                    jQuery(document).on('change', '.attachmentUploadTypeToggle', function() {
                        var isLink = jQuery('.attachmentUploadTypeToggle:checked').val() === 'link';
                        jQuery('.attachmentFileInput').toggle(!isLink);
                        jQuery('.attachmentLinkInput').toggle(isLink);
                    });
                });
            </script>
        </div>

        <input type="submit" name="upload" class="button" value="<?php echo $tpl->__('buttons.upload'); ?>" />

    </form>

    <div class="clear"></div>
</div>

<div class="mediamgr_content">

    <ul id='medialist' class='listfile'>
        <?php foreach ($tpl->get('files') as $file) { ?>
            <?php
            $isLink = strtolower($file['extension'] ?? '') === 'link';
            // For links the URL lives in encName. Escape for safe attribute output.
            $linkUrl = $isLink ? $tpl->escape($file['encName']) : '';
            $downloadUrl = BASE_URL . '/files/get?module=' . $file['module'] . '&encName=' . $file['encName'] . '&ext=' . $file['extension'] . '&realName=' . $file['realName'];
            ?>
            <li class="<?php echo $file['moduleId'] ?>">
                <div class="inlineDropDownContainer dropright" style="float:right;">

                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-header"><?php echo $isLink ? $tpl->__('subtitles.link') : $tpl->__('subtitles.file'); ?></li>
                        <?php if ($isLink) { ?>
                            <li><a href="<?php echo $linkUrl; ?>" target="_blank" rel="noopener noreferrer"><?php echo $tpl->__('links.open_link'); ?></a></li>
                        <?php } else { ?>
                            <li><a href="<?php echo $downloadUrl; ?>" target="_blank"><?php echo $tpl->__('links.download'); ?></a></li>
                        <?php } ?>

                        <?php
                        if ($login::userIsAtLeast($roles::$editor)) { ?>
                            <li><a href="<?= BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>?delFile=<?php echo $file['id'] ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete'); ?></a></li>
                        <?php } ?>

                    </ul>
                </div>

                <?php if ($isLink) { ?>
                    <a href="<?php echo $linkUrl; ?>" target="_blank" rel="noopener noreferrer">
                        <div style="font-size:50px; margin-bottom:10px;">
                            <span class="fa fa-link"></span>
                        </div>
                        <span class="filename"><?php echo $tpl->escape($file['realName']); ?></span>
                    </a>
                <?php } else { ?>
                    <a class="cboxElement" href="<?php echo $downloadUrl; ?>" target="_blank">
                        <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) {  ?>
                            <img style='max-height: 50px; max-width: 70px;' src="<?php echo $downloadUrl; ?>" alt="" />
                        <?php } else { ?>
                            <div style="font-size:50px; margin-bottom:10px;">
                                <span class="fa fa-file"></span>
                            </div>
                        <?php } ?>
                        <span class="filename"><?php echo $file['realName'] ?></span>
                    </a>
                <?php } ?>

            </li>
        <?php } ?>
        <br class="clearall" />
    </ul>

</div><!--mediamgr_content-->



<?php if (count($tpl->get('files')) == 0) { ?>
    <div class="text-center">
        <div style='width:33%' class='svgContainer'>
            <?php echo file_get_contents(ROOT . '/dist/images/svg/undraw_image__folder_re_hgp7.svg'); ?>
            <?php echo $tpl->__('text.no_files') ?>
        </div>
    </div>
<?php } ?>
<div style='clear:both'>&nbsp;</div>

<script type='text/javascript'>
    leantime.replaceSVGColors();
</script>
