

    <div id="fileManager">
    	<div >
    		
    		<?php echo $this->displayNotification() ?>

			<div class='mediamgr'>
			 <div class='mediamgr_left'>
                    <div class="mediamgr_category">
                    	
		        	<form action='<?=BASE_URL ?>/files/showAll<?php if(isset($_GET['modalPopUp'])) { echo"&modalPopUp=true"; }?>' method='post' enctype="multipart/form-data" class="fileModal" >
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
					   
					   <input type="submit" name="upload" class="button" value="<?php echo $this->__('UPLOAD'); ?>" />
		
					</form>

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
                                        <li><a target="_blank" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $this->__("links.download"); ?></a></li>

                                        <?php  if ($login::userIsAtLeast("developer")) { ?>
                                            <li><a href="<?=BASE_URL ?>/files/showAll?delFile=<?php echo $file['id'] ?>" class="delete deleteFile"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete"); ?></a></li>
                                        <?php  } ?>

                                    </ul>
                                </div>
                              	<a class="imageLink" href="<?=BASE_URL?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>">
                              		<?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))):  ?>
                              			<img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>" alt="" />
                              		<?php else: ?>
                              			<img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/images/thumbs/doc.png' />
                              		<?php endif; ?>
                            		<span class="filename"><?php echo substr($file['realName'], 0, 10)."(...).".$file['extension'] ?></span>
                              	</a>
                           	</li>
                           	<?php endforeach; ?>
                        <br class="clearall" />
                    	</ul>
                        <br class="clearall" />
                        
                    </div><!--mediamgr_content-->
                    
                </div><!--mediamgr_left -->
                

                <br class="clearall" />
            </div><!--mediamgr-->      		 	


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

            //Replaces data-rel attribute to rel.
            //We use data-rel because of w3c validation issue
            jQuery('a[data-rel]').each(function() {
                jQuery(this).attr('rel', jQuery(this).data('rel'));
            });

            //jQuery("#medialist a").colorbox();

            <?php if(isset($_GET['modalPopUp'])) { ?>

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

