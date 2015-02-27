<?php $lead = $this->get('lead'); ?>
<script type="text/javascript">

function optPressed() {
	jQuery(document).ready(function($) {
	  if ($("#refSource option:selected").val() == '5') {
	    $('.client').show();
	    $('.other').hide();
	  } else {
	    $('.client').hide();
	    $('.other').show();
	  }
	});
}
	
</script>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('LEAD_DETAILS'); ?></h5>
        <h1><?php echo $language->lang_echo('DELETE')." ".$lead['name'] ?></h1>
    </div>
</div><!--pageheader-->      
<div class="maincontent">
	<div class="maincontentinner">
		
	    <?php echo $this->displayNotification() ?>
	    <p><?php echo $language->lang_echo('CONFIRM_DELETE') ?></p><br/>
	    
		<form action='' method='POST'>
			<input type='submit' value='<?php echo $language->lang_echo('DELETE') ?>' name='delete' />
		</form>

	</div>
</div>