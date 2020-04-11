<?php
$ticket = $this->get('ticket');
?>
<div class="mediamgr_category">
    
    <form action='#files' method='POST' enctype="multipart/form-data" class="">
        <div class="par f-left" style="margin-right: 15px;">

         <div class='fileupload fileupload-new' data-provides='fileupload'>
                <input type="hidden" />
            <div class="input-append">
                <div class="uneditable-input span3">
                    <i class="iconfa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                </div>
                <span class="btn btn-file">
                    <span class="fileupload-new"><?php echo $this->__("buttons.select_file"); ?></span>
                    <span class='fileupload-exists'><?php echo $this->__("buttons.change"); ?></span>
                    <input type='file' name='file' />
                </span>
                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'><?php echo $this->__("buttons.remove"); ?></a>
            </div>
          </div>
       </div>

       <input type="submit" name="upload" class="button" value="<?php echo $this->__('buttons.upload'); ?>" />

    </form>
    <div class="clear"></div>
</div>

<div class="mediamgr_content">

    <ul id='medialist' class='listfile'>
    <?php foreach($this->get('files') as $file): ?>
        <li class="<?php echo $file['moduleId'] ?>">
            <div class="inlineDropDownContainer" style="float:right;">

                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                    <li class="nav-header"><?php echo $this->__("subtitles.file"); ?></li>
                    <li><a href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $this->__("links.download"); ?></a></li>

                    <?php  if ($login::userIsAtLeast("developer")) { ?>
                        <li><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id ?>?delFile=<?php echo $file['id'] ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete"); ?></a></li>
                    <?php  } ?>

                </ul>
            </div>


              <a class="cboxElement" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>">
                  <?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))) :  ?>
                      <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>" alt="" />
                    <?php else: ?>
                      <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/images/thumbs/doc.png' />
                    <?php endif; ?>
                <span class="filename"><?php echo $file['realName'] ?></span>
              </a>

           </li>
    <?php endforeach; ?>
    <br class="clearall" />
    </ul>

</div><!--mediamgr_content-->

<div style='clear:both'>&nbsp;</div>
