
<script type="text/javascript">
    jQuery(document).ready(function(){
        
        //Replaces data-rel attribute to rel.
        //We use data-rel because of w3c validation issue
        jQuery('a[data-rel]').each(function() {
            jQuery(this).attr('rel', jQuery(this).data('rel'));
        });
        
        jQuery("#medialist a").colorbox();
        
    });
    jQuery(window).load(function(){
        jQuery('#medialist').isotope({
            itemSelector : 'li',
            layoutMode : 'fitRows'
        });
        
        // Media Filter
        jQuery('#mediafilter a').click(function(){
	    
            var filter = (jQuery(this).attr('href') != 'all')? '.'+jQuery(this).attr('href') : '*';
            jQuery('#medialist').isotope({ filter: filter });
	    
            jQuery('#mediafilter li').removeClass('current');
            jQuery(this).parent().addClass('current');
	    
            return false;
        });
    });
</script>


	<div class="pageheader">
      	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
        	<input type="text" name="term" placeholder="To search type and hit enter..." />
        </form>
            
        <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
        <div class="pagetitle">
        	<h5><?php echo $language->lang_echo('FILE_DETAILS'); ?></h5>
            <h1><?php echo $language->lang_echo('FILES'); ?></h1>
        </div>
   	</div><!--pageheader-->
        
    <div class="maincontent">
    	<div class="maincontentinner">
    		
    		<?php echo $this->displayNotification() ?>
    		
            <div class="widget">
   				<h4 class="widgettitle"><?php echo $language->lang_echo('FILES'); ?></h4>
   				<div class="widgetcontent">

			<div class='mediamgr'>
			 <div class='mediamgr_left'><!--
                	<div class="mediamgr_head">
                    	<ul class="mediamgr_menu">
                        	<li><a class="btn prev prev_disabled"><span class="icon-chevron-left"></span></a></li>
                            <li><a class="btn next"><span class="icon-chevron-right"></span></a></li>
                            <li class="marginleft15"><a class="btn selectall"><span class="icon-check"></span> Select All</a></li>
                            <li class="marginleft15 newfoldbtn"><a class="btn newfolder" title="Add New Folder"><span class="icon-folder-open"></span></a></li>
                            <li class="marginleft5 trashbtn"><a class="btn trash" title="Trash"><span class="icon-trash"></span></a></li>
                            <li class="marginleft15 filesearch">
                            	<form>
                            		<input type="text" id="filekeyword" class="filekeyword" placeholder="Search file here" />
                                </form>
                            </li>
                            <li class="right newfilebtn"><a href="" class="btn btn-primary">Upload New File</a></li>
                        </ul>
                        <span class="clearall"></span>
                   </div>--><!--mediamgr_head-->	
                    <div class="mediamgr_category">
                    	
		        	<form action='#files' method='POST' enctype="multipart/form-data" class="f-left">
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
					
                    <div class="btn-group">
				    	<button id="widgetAction2" class="btn btn-primary"><?php echo $language->lang_echo('TYPE') ?><span class="caret"></span></button>
				    
				    	<ul id="mediafilter" class=" dropdown-menu widgetList2" style='display: none'>		
				    	  <li><a href="all">All</a></li>
                        	<?php foreach($this->get('modules') as $key => $module): ?>
	                        		<li class="<?php if($this->get('currentModule') == $key): ?>current<?php endif; ?>">
	                        			<?php echo $this->displayLink('files.showAll', $module, array('id' => $key)); ?>
	                        		</li>
                        	<?php endforeach; ?>
						</ul>
					</div>	                   	
                    <div class="btn-group">
				    	<button id="widgetAction" class="btn btn-primary">
				    		<?php if(isset($_GET['id'])): ?> 
				    			<?php echo $_GET['id'] ?>  
							<?php else: ?>
								<?php echo $language->lang_echo('MODULE') ?> 
				    		<?php endif; ?>
				    		<?php echo $language->lang_echo('FILTER') ?>
				    		<span class="caret"></span>
				    	</button>
				    
				    	<ul id="mediafilter" class=" dropdown-menu widgetList" style='display: none'>		
				    	  <li><a href="all">All</a></li>
						  <?php foreach($this->get('folders') as $folder): ?>
						  	<?php if (isset($folder['id']) && $folder['id'] > 0): ?>
		                       	<li><a href="<?php echo $folder['id'] ?>"><?php echo $folder['title'] ?></a></li>
	                       	<?php endif; ?>
						  <?php endforeach; ?>
						</ul>
					</div>							
					</div> 
                    
                   	<!--<div class="mediamgr_category">
                    	<ul id="mediafilter">
                        	<li class="current"><a href="all">All</a></li>
                            <?php foreach($this->get('folders') as $folder): ?>
	                            <li><a href="<?php echo $folder['id'] ?>"><?php echo $folder['title'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                   	</div>==?<!--mediamgr_category-->
                    
                    <div class="mediamgr_content">          
                    	
                    	<ul id='medialist' class='listfile'>
                    		<?php foreach($this->get('files') as $file): ?>
                    		<li class="<?php echo $file['moduleId'] ?>">
                              	<a href="/userdata/<?php echo $file['module'] ?>/<?php echo $file['encName'] ?>.<?php echo $file['extension'] ?>">
                              		<?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/'.$file['module'].'/'.$file['encName'].'.'.$file['extension']) && in_array(strtolower($file['extension']), $this->get('imgExtensions'))):  ?>
                              			<img style='max-height: 50px; max-width: 70px;' src="/userdata/<?php echo $file["module"] ?>/<?php echo $file['encName'] ?>.<?php echo $file["extension"] ?>" alt="" />
                              		<?php else: ?>
                              			<img style='max-height: 50px; max-width: 70px;' src='/userdata/file.png'
                              		<?php endif; ?>
                            		<span class="filename"><?php echo $file['realName'] ?></span>
                              	</a>
                           	</li>
                           	<?php endforeach; ?>
                    	</ul>
                        <br class="clearall" />
                        
                    </div><!--mediamgr_content-->
                    
                </div><!--mediamgr_left -->
                
                <div class="mediamgr_right">
                	<!--<div class="mediamgr_rightinner">
                        <h4>Type</h4>
                        <ul class="menuright">
                        	<?php foreach($this->get('modules') as $key => $module): ?>
                        		<?php if ( 
                        				($key != 'client' || ($_SESSION['userdata']['role'] == 2 || $_SESSION['userdata']['role'] == 4)) 
                        				&& ($key != 'lead' || ($_SESSION['userdata']['role'] == 2 || $_SESSION['userdata']['role'] == 4)) 
									): ?>
	                        		<li class="<?php if($this->get('currentModule') == $key): ?>current<?php endif; ?>">
	                        			<?php echo $this->displayLink('files.showAll', $module, array('id' => $key)); ?>
	                        		</li>
                        	<?php endif; ?>
                        	<?php endforeach; ?>
                        </ul>
                    </div><!-- mediamgr_rightinner -->
                </div><!-- mediamgr_right -->
                <br class="clearall" />
            </div><!--mediamgr-->      		 	

			</div>
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
