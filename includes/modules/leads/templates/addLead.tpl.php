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

<style type='text/css'>
/*	.client { display: none; }
	.show { display: block !important; }*/
</style>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('LEAD_DETAILS'); ?></h5>
        <h1><?php echo $language->lang_echo('ADD_LEAD'); ?></h1>
    </div>
</div><!--pageheader-->      
<div class="maincontent">
	<div class="maincontentinner">
	            	
	    <?php echo $this->displayNotification() ?>
	            	
		<form action='' method='POST' class='stdform'>
			
		<div class="widget widget-half">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
		   <div class="widgetcontent">

			<label for="name"><?php echo $language->lang_echo('LEAD_NAME') ?>:</label>
			<input type='text' value='' name='name' /><br/>
			
			<div class='par'>
				<label for="money"><?php echo $language->lang_echo('POTENTIAL_MONEY') ?>:</label>
				<div class='input-prepend input-append'>
					<span class='add-on'>$</span>
					<input type='text' class='span2' id='appendedPrependedInput' value='' placeholder='0' name='money' style='text-align: right' />
					<span class='add-on'>.00</span>
				</div>
			</div>
			
			<!--<label for="proposal"><?php echo $language->lang_echo('PROPOSAL') ?>:</label>
			<textarea rows='5' style='width: 400px;' name='proposal'></textarea><br/>-->
			
			<label for="referralSource"><?php echo $language->lang_echo('REFERRAL_SOURCE') ?>:</label>
			<select name='referralSource' id='refSource' onchange="optPressed()">
				<?php foreach ($this->get('referralSources') as $source): ?>
					<option value='<?php echo $source['id'] ?>' class='<?php echo $source['title'] ?>'><?php echo $source['title'] ?></option>
				<?php endforeach; ?>
			</select><br/>
			
			<p>
				<label><?php echo $language->lang_echo('EXPLANATION') ?>:</label>
				<textarea class='other' name='referralValueOther' rows='5' style='width: 400px;'></textarea><br/>
			</p>
			
			<label for="referralValueClient" class='client' style='display: none;'><?php echo $language->lang_echo('CLIENT') ?></label>
			<select name='referralValueClient' class='client' style='display: none;'>
				<option value='-1'><?php echo $language->lang_echo('NO_ASSIGNMENT') ?></option>
				<?php foreach ($this->get('clients') as $client): ?>
					<option value='<?php echo $client['id'] ?>'><?php echo $client['name'] ?></option>
				<?php endforeach; ?>
			</select><br/>

			<p class="stdformbutton">
				<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
				<input type="reset" class="btn" value="<?php echo $language->lang_echo('RESET_BUTTON') ?>" />
			</p>
			
			</div>
		</div>
			
		<div class="widget widget-half">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('CONTACT'); ?></h4>
		   <div class="widgetcontent">
			<p>
			<label for='name'><?php echo $language->lang_echo('NAME') ?></label>			
			<input type='text' value='' name='clientName' /><br/>
			</p>
			
			<p>
			<label for='email'><?php echo $language->lang_echo('EMAIL') ?></label>			
			<input type='text' value='' name='email' /><br/>
			</p>

			<p>
			<label for='internet'><?php echo $language->lang_echo('INTERNET') ?></label>			
			<input type='text' value='' name='internet' /><br/>
			</p>
			
			<p>
			<label for='street'><?php echo $language->lang_echo('STREET') ?></label>			
			<input type='text' value='' name='street' />
			</p>
			
			<p>
			<label for='zip'><?php echo $language->lang_echo('ZIP') ?></label>			
			<input type='text' value='' name='zip' />
			</p>
			
			<p>
			<label for='city'><?php echo $language->lang_echo('CITY') ?></label>			
			<input type='text' value='' name='city' />
			</p>
			
			<p>
			<label for='state'><?php echo $language->lang_echo('STATE') ?></label>			
			<input type='text' value='' name='state' />
			</p>
			
			<p>
			<label for='country'><?php echo $language->lang_echo('COUNTRY') ?></label>			
			<input type='text' value='' name='country' />
			</p>
			
			<p>
			<label for='phone'><?php echo $language->lang_echo('PHONE') ?></label>			
			<input type='text' value='' name='phone' />
			</p>
			</div>
		</div>

		</form>
	
	</div>
</div>