<?php  $helper = $this->get('helper'); 

	application::addToSection("/asdf/asdf.css", "FOOTER", "CSS");

?>
		<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('TILES') ?></h5>
                <h1><?php echo $language->lang_echo('DASHBOARD') ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>
				
				<?php /*
				<div class="btn-group">
			    	<button id="widgetAction" class="btn btn-primary"><?php echo $language->lang_echo('TILES') ?>  <span class="caret"></span></button>
			    	<form action='' method="POST">
			    
			    	<ul id="widgetList" class="dropdown-menu widgetList" style='display: none'>		
					  <?php foreach ($this->get('widgetTypes') as $widget): ?>
					  	<li>
					  		<input class='clear' type='checkbox' name='widget-<?php echo $widget['id'] ?>' 
					  			<?php if (in_array($widget['id'],$this->get('availableWidgets'))): ?>
					  				 checked='checked' 
					  			<?php endif; ?>/>
					  		<label for='widget-<?php echo $widget['id'] ?>'><?php echo $widget['title'] ?></label>
					  	</li>
					  <?php endforeach; ?>
					  <li style='margin-top:5px;' class='a-center' >
					  	<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='updateWidgets' />
					  </li>
					</ul>
					</form>
				</div>				
				 */ ?>
				<div class='clear'>&nbsp;</div>

				<?php foreach ($this->get('widgets') as $widget): ?>
				<div class="widgetbox widgetleft">
    			 	<h4 class="widgettitle"><?php echo $widget['title'] ?> <a class="close">×</a></h4>
        		 	<div class="widgetcontent">
						<?php echo $this->displaySubmodule($widget['submoduleAlias']) ?>
					</div>
				</div>
				<?php endforeach; ?>		

			</div>
		</div>

<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery('#widgetAction').click(function(){
			jQuery('#widgetList').toggle();
		});
	});
</script>
