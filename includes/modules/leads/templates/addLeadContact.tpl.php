<?php $lead = $this->get('lead'); ?>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="iconfa-laptop"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('ADD_LEAD_CONTACT'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
	<div class="maincontentinner">
		
		<?php echo $this->displayNotification() ?>
		
		<form action='' method='POST'>

			<label for='street'><?php echo $language->lang_echo('STREET') ?></label>			
			<input type='text' value='' name='street' />
			
			<label for='zip'><?php echo $language->lang_echo('ZIP') ?></label>			
			<input type='text' value='' name='zip' />
			
			<label for='city'><?php echo $language->lang_echo('CITY') ?></label>			
			<input type='text' value='' name='city' />
			
			<label for='state'><?php echo $language->lang_echo('STATE') ?></label>			
			<input type='text' value='' name='state' />
			
			<label for='country'><?php echo $language->lang_echo('COUNTRY') ?></label>			
			<input type='text' value='' name='country' />
			
			<label for='phone'><?php echo $language->lang_echo('PHONE') ?></label>			
			<input type='text' value='' name='phone' />
			
			<label for='internet'><?php echo $language->lang_echo('INTERNET') ?></label>			
			<input type='text' value='' name='internet' /><br/>
			
			<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
			
		</form>		

	</div>
</div>