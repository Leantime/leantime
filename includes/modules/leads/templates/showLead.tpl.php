<?php 

$helper = new helper();
$lead = $this->get('lead'); 
$primeContact = $this->get('contactInfo');
?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
    		$('.tabbedwidget').tabs(); 
 	});
</script>

<div class="pageheader">
   	<form action="index.php?act=tickets.showAll" method="post" class="searchbar">
    	<input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>
            
    <div class="pageicon"><span class="iconfa-laptop"></span></div>
    <div class="pagetitle">
    	<h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('LEADS'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
	<div class="maincontentinner">		

		<?php echo $this->displayNotification() ?>

		<div class="tabbedwidget tab-primary">
			
			<ul>
				<li><a href="#general"><?php echo $language->lang_echo('LEAD_DETAILS') ?></a></li>
				<li><a href="#primecontact"><?php echo $language->lang_echo('PRIMARY_CONTACT') ?></a></li>
				<li><a href="#comments"><?php echo $language->lang_echo('COMMENTS') ?> (<?php echo count($this->get('comments')) ?>)</a></li>
				<li><a href="#files"><?php echo $language->lang_echo('FILES') ?> (<?php echo count($this->get('files')) ?>)</a></li>
				<!--<li><a href="#statistics"><?php echo $language->lang_echo('STATISTICS') ?></a></li>-->
				<li><a href="#communication"><?php echo $language->lang_echo('COMMUNICATION') ?></a></li>
				<div class="btn-group">
			    	<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><?php echo $language->lang_echo('ACTION') ?> <span class="caret"></span></button>
			        <ul class="dropdown-menu">		
		  				<li><?php echo $this->displayLink('leads.editLead', $language->lang_echo('EDIT_LEAD'), array('id' => $lead['id'])) ?></li>
						<li><?php echo $this->displayLink('leads.deleteLead', $language->lang_echo('DELETE_LEAD'), array('id' => $lead['id'])) ?></li>
						<!--<li><?php echo $this->displayLink('clients.editClient', $language->lang_echo('EDIT_CONTACT_INFO'), array('id' => $lead['clientId'])) ?></li>-->
						<!--<li><?php echo $this->displayLink('leads.addLeadContact', $language->lang_echo('ADD_CONTACT_INFO'), array('id' => $lead['id'])) ?></li>-->
					</ul>
				</div>				
			</ul>

			<div id='general'>
				
				<table class='table table-bordered'>
					<colgroup>
						<col class='con0' />
						<col class='con1' />
						<col class='con0' />
						<col class='con1' />
						<col class='con0' />
					</colgroup>
					<thead>
						<tr>
							<th class="head0"><?php echo $language->lang_echo('ID') ?></th>
							<th class="head1"><?php echo $language->lang_echo('NAME') ?></th>
							<th class="head0"><?php echo $language->lang_echo('REFERRAL') ?></th>
							<th class="head1"><?php echo $language->lang_echo('STATUS') ?></th>
							<th class="head0"><?php echo $language->lang_echo('POTENTIAL_MONEY') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $lead['id'] ?></td>
							<td><?php echo $lead['name'] ?></td>
							<td><?php echo $lead['refSource'] ?></td>
							<td><?php echo $lead['status'] ?></td>
							<td><?php echo $lead['potentialMoney'] ?></td>
						</tr>
					</tbody>
				</table>
				
				<p>
					<strong><?php echo $language->lang_echo('POTENTIAL_MONEY') ?></strong>
					<?php echo $lead['potentialMoney'] ?><br/>
					
					<strong><?php echo $language->lang_echo('REFERRAL') ?></strong>
					<?php echo $lead['refSource'] ?><br/>
					
					<strong><?php echo $language->lang_echo('STATUS') ?></strong>
					<?php echo $lead['status'] ?><br/>
				</p>
				
				<p>
					<strong><?php echo $language->lang_echo('PROPOSAL') ?></strong>
					<?php echo $lead['proposal'] ?>
				</p>
				
			</div>
			<div id='primecontact'>
				
				<strong><?php echo $language->lang_echo('EMAIL') ?>:</strong>
				<?php echo $primeContact['email'] ?><br/>
				
				<strong><?php echo $language->lang_echo('STREET') ?>:</strong>
				<?php echo $primeContact['street'] ?><br/>
				
				<strong><?php echo $language->lang_echo('ZIP') ?>:</strong>
				<?php echo $primeContact['zip'] ?><br/>
				
				<strong><?php echo $language->lang_echo('CITY') ?>:</strong>
				<?php echo $primeContact['city'] ?><br/>
				
				<strong><?php echo $language->lang_echo('STATE') ?>:</strong>
				<?php echo $primeContact['state'] ?><br/>
				
				<strong><?php echo $language->lang_echo('COUNTRY') ?>:</strong>
				<?php echo $primeContact['country'] ?><br/>
				
				<strong><?php echo $language->lang_echo('PHONE') ?>:</strong>
				<?php echo $primeContact['phone'] ?><br/>
				
			</div>
			<div id='comments'>
				
				<?php echo $this->displaySubmodule('comments-generalComment') ?>

			</div>
			<div id='files'>
			
				<div class="widgetbox widgetright">
			    	<h4 class="widgettitle"><?php echo $language->lang_echo('UPLOAD') ?> <a class="close">×</a></h4>
			        <div class="widgetcontent">
				        <form action='#files' method='POST' enctype="multipart/form-data">
				        	
						<div class="par">
						
						 <label><?php echo $language->lang_echo('UPLOAD_FILE') ?></label>
					   	 
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
					   
						<p class='stdformbutton'>
							<input type="submit" name="upload" class="button" value="<?php echo $language->lang_echo('UPLOAD'); ?>" />
						</p>		        	
				        	
						</form>	
			        </div>
			    </div>
			
				<?php foreach($this->get('files') as $rowFiles): ?> 
					<a href="/userdata/<?php echo $rowFiles['encName']; ?>" target="_blank">
						<?php echo $rowFiles['realName']; ?>
					</a><br />
			
					<?php printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", 
							$rowFiles['firstname'], $rowFiles['lastname'], $helper->timestamp2date($rowFiles['date'], 2)); ?>
			
					<?php if($this->get('role') === 'admin'): ?> 
						| <a href="index.php?act=projects.showProject&amp;id=<?php echo $project['id']; ?>&amp;delFile=<?php echo $rowFiles['encName']; ?>#anhanege"><?php echo $lang['DELETE']; ?></a>
					<?php endif; ?> 
					<br /><hr /><br />
			
				<?php endforeach; ?> 
				<?php if(count($this->get('files')) == 0): ?> 
					<?php echo $language->lang_echo('ERROR_NO_FILES'); ?>
				<?php endif; ?>
				
				<div style='clear:both'>&nbsp;</div>				
			</div>
			<div id="communication">
				
			</div>
		</div>		
	</div>
</div>