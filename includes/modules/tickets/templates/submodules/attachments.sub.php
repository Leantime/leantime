<?php 

$objTicket = $this->get('objTicket'); 
$ticket = $this->get('ticket');
$helper = new helper();

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
                              	<a class="cboxElement" href="/userdata/<?php echo $file['module'] ?>/<?php echo $file['encName'] ?>.<?php echo $file['extension'] ?>">
                              		<?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/'.$file['module'].'/'.$file['encName'].'.'.$file['extension']) && in_array(strtolower($file['extension']), $this->get('imgExtensions'))):  ?>
                              			<img style='max-height: 50px; max-width: 70px;' src="/userdata/<?php echo $file["module"] ?>/<?php echo $file['encName'] ?>.<?php echo $file["extension"] ?>" alt="" />
                              		<?php else: ?>
                              			<img style='max-height: 50px; max-width: 70px;' src='/userdata/file.png' />
                              		<?php endif; ?>
                            		<span class="filename"><?php echo $file['realName'] ?></span>
                              	</a>
                           	</li>
                           	<?php endforeach; ?>
                        <br class="clearall" />
                    	</ul>
                        
                    </div><!--mediamgr_content-->

<div style='clear:both'>&nbsp;</div>
