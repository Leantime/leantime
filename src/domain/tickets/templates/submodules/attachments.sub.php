<?php 

$objTicket = $this->get('objTicket'); 
$ticket = $this->get('ticket');
$helper = $this->get('helper');

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
                            <span class="fileupload-new">Select file</span>
                            <span class='fileupload-exists'>Change</span>
                            <input type='file' name='file' />
                        </span>    
                        <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'>Remove</a>
                    </div>
                  </div>        
               </div>
               
               <input type="submit" name="upload" class="button" value="<?php echo $language->lang_echo('UPLOAD'); ?>" />

            </form>    
            <div class="clear"></div>
</div>
                      <div class="mediamgr_content">          
                        
                        <ul id='medialist' class='listfile'>
                        <?php foreach($this->get('files') as $file): ?>
                            <li class="<?php echo $file['moduleId'] ?>">
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
