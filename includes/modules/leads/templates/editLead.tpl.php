<?php 

$lead = $this->get('lead'); 
$client = $this->get('client');
?>
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
        <h1><?php echo $language->lang_echo('EDIT_LEAD'); ?></h1>
    </div>
</div><!--pageheader-->      
<div class="maincontent">
	<div class="maincontentinner">
	            	
	    <?php echo $this->displayNotification() ?>
	            	
		<form action='' method='POST' class="stdform" enctype="multipart/form-data">
					
		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
		   <div class="widgetcontent">
			
			<label for="name"><?php echo $language->lang_echo('LEAD_NAME') ?>:</label>
			<input type='text' value='<?php echo $lead['name'] ?>' placeholder='<?php echo $language->lang_echo('LEAD_NAME') ?>' name='name' /><br/>
			
			<label for="status"><?php echo $language->lang_echo('STATUS') ?>:</label>
			<select name="status">
				<?php foreach($this->get('status') as $key => $name): ?>
					<option value='<?php echo $key ?>' 
						<?php if($key == $lead['status']): ?>selected='selected'<?php endif; ?>
					><?php echo $name ?></option>
				<?php endforeach; ?>
			</select>
			
			
			<div class='par'>
				<label for="money"><?php echo $language->lang_echo('POTENTIAL_MONEY') ?>:</label>
				<div class='input-prepend input-append'>
					<span class='add-on'>$</span>
					<input type='text' class='span2' id='appendedPrependedInput' value='<?php echo $lead['potentialMoney'] ?>' placeholder='0' name='money' style='text-align: right' />
					<span class='add-on'>.00</span>
				</div>
			</div>
			
			<div class='par'>
				<label for="money"><?php echo $language->lang_echo('ACTUAL_MONEY') ?>:</label>
				<div class='input-prepend input-append'>
					<span class='add-on'>$</span>
					<input type='text' class='span2' id='appendedPrependedInput' value='<?php echo $lead['actualMoney'] ?>' placeholder='0' name='actualMoney' style='text-align: right' />
					<span class='add-on'>.00</span>
				</div>
			</div>
			<!--
			<div class="par">
			 <label><?php echo $language->lang_echo('PROPOSAL') ?></label>
		   	 
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
		   			
			<label for="proposal"><?php echo $language->lang_echo('PROPOSAL') ?>:</label>
			<textarea rows='5' style='width: 400px;' name='proposal'><?php echo $lead['proposal'] ?></textarea><br/>-->
			
			<label for="referralSource"><?php echo $language->lang_echo('REFERRAL_SOURCE') ?>:</label>
			<select name='referralSource' id='refSource' onchange="optPressed()">
				<?php foreach ($this->get('referralSources') as $source): ?>
					<option value='<?php echo $source['id'] ?>' class='<?php echo $source['title'] ?>'
						<?php if ($lead['refSource'] == $source['id']): ?>selected='selected'<?php endif; ?>	
					><?php echo $source['title'] ?></option>
				<?php endforeach; ?>
			</select><br/>
			
			<label for="referralValueClient" class='client' style='display: none;'><?php echo $language->lang_echo('CLIENT') ?></label>
			<select name='referralValueClient' class='client' style='display: none;'>
				<option value='-1'><?php echo $language->lang_echo('NO_ASSIGNMENT') ?></option>
				<?php foreach ($this->get('clients') as $clients): ?>
					<option value='<?php echo $clients['id'] ?>' 
						<?php if($lead['refValue'] == $clients['id']): ?>selected='selected'<?php endif; ?>
					><?php echo $clients['name'] ?></option>
				<?php endforeach; ?>
			</select><br/>
			
			<label for="referralValueOther" class='other'><?php echo $language->lang_echo('EXPLANATION') ?>:</label>
			<textarea class='other' name='referralValueOther' rows='5' style='width: 400px;'><?php if($lead['refSource'] != 5): ?><?php echo $lead['refValue']; ?><?php endif; ?></textarea><br/>
			
			</div>
		</div>
		<div class="widget">
		   <h4 class="widgettitle"><?php echo $language->lang_echo('CONTACT'); ?></h4>
		   <div class="widgetcontent">
		   			
			<p>
			<label for='name'><?php echo $language->lang_echo('NAME') ?></label>			
			<input type='text' value='<?php echo $client['name'] ?>' name='clientName' /><br/>
			</p>
			
			<p>
			<label for='email'><?php echo $language->lang_echo('EMAIL') ?></label>			
			<input type='text' value='<?php echo $client['email'] ?>' name='email' /><br/>
			</p>
						
			<p>
			<label for='internet'><?php echo $language->lang_echo('INTERNET') ?></label>			
			<input type='text' value='<?php echo $client['internet'] ?>' name='internet' /><br/>
			</p>
			
			<p>
			<label for='street'><?php echo $language->lang_echo('STREET') ?></label>			
			<input type='text' value='<?php echo $client['street'] ?>' name='street' />
			</p>
			
			<p>
			<label for='zip'><?php echo $language->lang_echo('ZIP') ?></label>			
			<input type='text' value='<?php echo $client['zip'] ?>' name='zip' />
			</p>
			
			<p>
			<label for='city'><?php echo $language->lang_echo('CITY') ?></label>			
			<input type='text' value='<?php echo $client['city'] ?>' name='city' />
			</p>
			
			<p>
			<label for='state'><?php echo $language->lang_echo('STATE') ?></label>			
			<input type='text' value='<?php echo $client['state'] ?>' name='state' />
			</p>
			
			<p>
			<label for='country'><?php echo $language->lang_echo('COUNTRY') ?></label>			
			<input type='text' value='<?php echo $client['country'] ?>' name='country' />
			</p>
			
			<p>
			<label for='phone'><?php echo $language->lang_echo('PHONE') ?></label>			
			<input type='text' value='<?php echo $client['phone'] ?>' name='phone' />
			</p>
			
			<p class="stdformbutton">
				<input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
			</p>			

			</div>
		</div>
		</form>
	
	</div>
</div>

<script type='text/javascript'>
	optPressed();
</script>
