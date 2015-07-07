<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$project = $this->get('project');
$bookedHours = $this->get('bookedHours');
$helper = $this->get('helper');
$state = $this->get('state');
?>


<script type="text/javascript">
	jQuery(document).ready(function() { 
		 	toggleCommentBoxes(0);
		 
			jQuery('.tabbedwidget').tabs();
        	
/*			jQuery('#commentList').pager('div');*/
 			
			jQuery("#progressbar").progressbar({
				value: <?php echo $this->get('projectPercentage') ?>
			});
		
			jQuery("#accordion").accordion({
				autoHeight: false,
				navigation: true
			});

			jQuery("#dateFrom, #dateTo").datepicker({
				
				dateFormat: 'dd.mm.yy',
				dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
				dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
				monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
			});

    	} 
	); 

function toggleCommentBoxes(id){
		
		jQuery('.commentBox').hide('fast',function(){

			jQuery('.commentBox textarea').remove(); 

			jQuery('#comment'+id+'').prepend('<textarea rows="5" cols="50" name="text"></textarea>');
				
		}); 

		jQuery('#comment'+id+'').show('slow');		

		
	}
</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('PROJECT') ?> #<?php echo $project['id'] ?> | <?php echo $project['name']; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


				<?php echo $this->displayNotification() ?>

<!--<div id="tabs">-->
<div class="tabbedwidget tab-primary">


<ul>
	<li><a href="#projectdetails"><?php echo $language->lang_echo('PROJECT_DETAILS'); ?></a></li>
	<!--<li><a href="#progress"><?php echo $language->lang_echo('PROGRESS'); ?></a></li>-->
	<li><a href="#accounts"><?php echo $language->lang_echo('ACCOUNTS'); ?></a></li>
	<?php if ($this->displaySubmoduleTitle('projects-timesheet') != ''): ?>
	 <li><a href="#timesheets"><?php echo $this->displaySubmoduleTitle('projects-timesheet') ?></a></li>
	<?php endif; ?>
	<li><a href="#files"><?php echo $language->lang_echo('FILES'); ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
	<li><a href="#comment"><?php echo $language->lang_echo('COMMENTS'); ?> (<?php echo $this->get('numComments'); ?>)</a></li>
	<?php if ($this->displaySubmoduleTitle('projects-tickets') != ''): ?>
	 <li><a href='#tickets'><?php echo $this->displaySubmoduleTitle('projects-tickets') ?></a></li>
	<?php endif; ?>
	
	<?php if ($this->displaySubmoduleTitle('projects-budgeting') != ''): ?>
	 <li><a href="#budgeting"><?php echo $this->displaySubmoduleTitle('projects-budgeting') ?></a></li>
	<?php endif; ?>
	
	
	
	<?php if ($this->displayLink('projects.editProject','x') !== false || $this->displayLink('projects.delProject','x') !== false): ?>
		<div class="btn-group">
	    	<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><?php echo $language->lang_echo('ACTION') ?> <span class="caret"></span></button>
	        <ul class="dropdown-menu">		
				<li><?php echo $this->displayLink('projects.editProject', $language->lang_echo('EDIT'), array('id' => $project['id'])) ?></li>
				<li><?php echo $this->displayLink('projects.delProject', $language->lang_echo('DELETE'), array('id' => $project['id'])) ?></li>			
			</ul>
		</div>
	<?php endif; ?>	
</ul>

<div id="projectdetails">
	
	Progress
	<div class="progress progress-danger progress-striped active" style='width: 100% border: 1px solid #333; height:30px;'>
		<div style="width: <?php echo $this->get('projectPercentage');?>%" class="bar">&nbsp;</div>
	</div>
	<p>
		<strong><?php echo $project['name']; ?></strong><br /><br />
		<?php echo $project['details']; ?> <br />
	</p>
	<p>
		<?php echo $language->lang_echo('SUM_TICKETS') ?>: <?php echo $project['numberOfTickets']; ?><br />
		<?php echo $language->lang_echo('SUM_OPEN_TICKETS') ?>: <?php echo $this->get('openTickets'); ?><br />
	</p>

</div>

<div id="accounts">

<br/><a id="addAccount" class="btn btn-primary btn-rounded" style='cursor: pointer'><?php echo $language->lang_echo('ADD_ACCOUNT') ?></a>

<div id='accountForm' style='display: none'>
	<form action="#accounts" method="post" class="stdform">
		
	<p>
	<label for="accountName"><?php echo $language->lang_echo('LABEL'); ?></label> 
	<span class='field'>
	<input type="text" name="accountName" id="accountName" value="" /><br />
	</span></p>
	
	<p>
	<label for="kind"><?php echo $language->lang_echo('ACCOUNT_KIND'); ?></label> 
	<span class='field'>
	<input type="text" name="kind" id="kind" value="" /><br />
	</span></p>
	
	<p>
	<label for="username"><?php echo $language->lang_echo('USERNAME'); ?></label> 
	<span class='field'>
	<input type="text" name="username" id="username" value="" /><br />
	</span></p>
	
	<p>
	<label for="password"><?php echo $language->lang_echo('PASSWORD'); ?></label> 
	<span class='field'>
	<input type="text" name="password" value="" /><br />
	</span></p>
	
	<p>
	<label for="host"><?php echo $language->lang_echo('HOST'); ?></label> 
	<span class='field'>
	<input type="text" id="host" name="host" value="" /><br />
	</span></p>
	
	<p class='stdformbutton'>
		<input type="submit" name="accountSubmit" class="button" value="<?php echo $language->lang_echo('SUBMIT'); ?>" />
	</p>
	
	</form>	
</div>

<div id="accordion">
<?php if (count($this->get('accounts'))):  ?>
	<?php foreach($this->get('accounts') as $rowAccount): ?>
	
		<h3><a href="javascript:void(0);"><?php echo $rowAccount['name']; ?></a></h3>
		<div>
			<p>
			<strong><?php echo $lang['ACCOUNT_KIND']; ?>:</strong> <?php echo $rowAccount['kind']; ?><br />
			<strong><?php echo $lang['ACCOUNT_USERNAME']; ?>:</strong> <?php echo $rowAccount['username']; ?><br />
			<strong><?php echo $lang['ACCOUNT_PASSWORD']; ?>:</strong> <?php echo $rowAccount['password']; ?><br />
			<strong><?php echo $lang['ACCOUNT_HOST']; ?>:</strong> <?php echo $rowAccount['host']; ?><br />
			<br />
			
			<!--<a href="index.php?act=projects.showProject&amp;id=<?php echo $project['id']; ?>&amp;delAccount=<?php echo $rowAccount['id']; ?>"><?php echo $lang['DELETE_ACCOUNT']; ?></a>-->
			<?php echo $this->displayLink('projects.editAccount', $language->lang_echo('EDIT_ACCOUNT'), array('id' => $rowAccount['id'])); ?>
			
			</p>
		</div>
	<?php endforeach; ?>
	<?php endif; ?>
</div>
</div>

<div id="files">

<div class="mediamgr_category">
	        <form action='#files' method='POST' enctype="multipart/form-data">
	        	
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
</div>


                    <div class="mediamgr_content">          
                    	
                    	<ul id='medialist' class='listfile'>
                    		<?php foreach($this->get('files') as $file): ?>
                    		<li class="<?php echo $file['moduleI¨d'] ?>">
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
	
</div><!-- end files -->

<div id="comment">
 <?php echo $this->displaySubmodule('comments-generalComment') ?>
</div>

<div id="timesheets">
 <?php echo $this->displaySubmodule('projects-timesheet') ?>
</div>

<div id="budgeting">
 <?php echo $this->displaySubmodule('projects-budgeting') ?>
</div>


<div id='tickets'>
 <?php echo $this->displaySubmodule('projects-tickets') ?>
</div>

</div>


</div>
</div>
</div>

<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery('#addAccount').click(function() {
			jQuery('#accountForm').toggle();
			jQuery('#accordion').toggle();
		});
	});
</script>
