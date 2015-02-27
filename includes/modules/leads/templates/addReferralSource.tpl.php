<script type="text/javascript">

jQuery(document).ready(function($) {
  	$('#comments').pager('div');
}); 
</script>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="iconfa-laptop"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('ADD_REFERRAL_SOURCE'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
	<div class="maincontentinner">
	            	
		<form action='' method='POST'>
			
			<input type='text' value='' placeholder='Alias' name='alias' /><br/>
			
			<input type='text' value='' placeholder='Title' name='title' /><br/>
			
			<input type='submit' name='save' value='<?php echo $language->lang_echo('SAVE') ?>' />
			
		</form>
	
	</div>
</div>