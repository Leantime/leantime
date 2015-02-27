<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$client = $this->get('client');
$users = new users();
?>
<script type="text/javascript">
	jQuery(document).ready(function($) 
    	{
    		$('.tabbedwidget').tabs(); 
        	$('#comments').pager('div');
    	} 
	); 
    
</script>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('CLIENT_DETAILS'); ?></h5>
                <h1><?php echo $client['name']; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<div class="tabbedwidget tab-primary">
	
<ul>
	<li><a href="#clientDetails"><?php echo $language->lang_echo('CLIENT_DETAILS'); ?></a></li>
	<li><a href="#contacts"><?php echo $language->lang_echo('CONTACTS'); ?></a></li>
	<li><a href="#comment"><?php echo $language->lang_echo('COMMENTS'); ?> (<?php echo count($this->get('comments')) ?>)</a></li>
	<li><a href="#files"><?php echo $language->lang_echo('FILES'); ?> (<?php echo count($this->get('files')) ?>)</a></li>
	<li><a href="#projects"><?php echo $language->lang_echo('PROJECTS'); ?></a></li>
	
	<?php if ($this->displayLink('clients.editClient','x') !== false || $this->displayLink('clients.delClient','x') !== false): ?>
		<div class="btn-group">
	    	<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><?php echo $language->lang_echo('ACTION') ?> <span class="caret"></span></button>
	        <ul class="dropdown-menu">		
				<li><?php echo $this->displayLink('clients.editClient', $language->lang_echo('EDIT'), array('id'=>$client['id'])) ?></li>
				<li><?php echo $this->displayLink('clients.delClient', $language->lang_echo('DELETE'), array('id'=>$client['id'])) ?></li>		
			</ul>
		</div>
	<?php endif; ?>		
</ul>

<div id='clientDetails'>
	<p>
		<strong><?php echo $language->lang_echo('CLIENT_DETAILS'); ?>:</strong> <?php echo $client['street']; ?><br />
		<strong><?php echo $language->lang_echo('ZIP'); ?>, <?php echo $language->lang_echo('CITY'); ?>:</strong>
		<?php echo ''.$client['zip'].', '.$client['city'].''; ?><br />
		<strong><?php echo $language->lang_echo('STATE'); ?>, <?php echo $language->lang_echo('COUNTRY'); ?>:</strong>
		<?php echo ''.$client['state'].', '.$client['country'].''; ?><br /><br />
		<strong><?php echo $language->lang_echo('PHONE'); ?>:</strong> <?php echo $client['phone']; ?><br />
		<strong><?php echo $language->lang_echo('URL'); ?>:</strong> 
		<a href="http://<?php echo $client['internet'] ?>"><?php echo $client['internet']; ?></a>
	</p>
</div>

<div id='contacts'>
	
	<table class='table table-bordered'>
		 <colgroup>
	      	  <col class="con0"/>
	          <col class="con1" />
	      	  <col class="con0"/>
	          <col class="con1" />
		 </colgroup>
		 <thead>
		 	<tr>
		 		<th><?php echo $language->lang_echo('ID') ?></th>
		 		<th><?php echo $language->lang_echo('NAME') ?></th>
		 		<th><?php echo $language->lang_echo('EMAIL') ?></th>
		 		<th><?php echo $language->lang_echo('PHONE') ?></th>
		 	</tr>
		 </thead>
		 <tbody>
		 <?php foreach($this->get('userClients') as $user): ?>
			<tr>
				<td><?php echo $user['id'] ?></td>
				<td><?php echo $user['firstname']. ' ' .$user['lastname'] ?></td>
				<td><a href='mailto:<?php echo $user['username'] ?>'><?php echo $user['username'] ?></a></td>
				<td><?php echo $user['phone'] ?></td>
			</tr>
		 <?php endforeach; ?>
		 </tbody>
	</table>
	
</div>

<div id='comment'>
	
	<?php $this->displaySubmodule('comments-generalComment') ?>
	
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

    <div class="mediamgr_content">          
                    	
    	<ul id='medialist' class='listfile'>
        	<?php foreach($this->get('files') as $file): ?>
             <li class="<?php echo $file['moduleId'] ?>">
             	<a href="/userdata/<?php echo $file['module'] ?>/<?php echo $file['encName'] ?>.<?php echo $file['extension'] ?>" class="cboxElement">
                	<?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/'.$file['module'].'/'.$file['encName'].'.'.$file['extension']) && in_array(strtolower($file['extension']), $this->get('imgExtensions'))):  ?>
                    	<img style='max-height: 50px; max-width: 70px;' src="/userdata/<?php echo $file["module"] ?>/<?php echo $file['encName'] ?>.<?php echo $file["extension"] ?>" alt="" />
                    <?php else: ?>
                    	<img style='max-height: 50px; max-width: 70px;' src='/userdata/file.png'
                    <?php endif; ?>
	                <span class="filename"><?php echo $file['realName'] ?></span>
               	</a>
             </li>
            <?php endforeach; ?>
            <br class="clearall" />
         </ul>
                        
    </div><!--mediamgr_content-->
	<div style='clear:both'>&nbsp;</div>
	
	
</div>

<div id='projects'>
	<?php echo $this->displayLink('projects.newProject', $language->lang_echo('NEW_PROJECT'), NULL, array('class' => 'btn btn-primary btn-rounded')) ?><br/>
	<table class='table table-bordered'>
	 <colgroup>
      	  <col class="con0"/>
          <col class="con1" />
      	  <col class="con0"/>
          <col class="con1" />
	 </colgroup>
	 <thead>
	 	<tr>
	 		<th><?php echo $language->lang_echo('ID') ?></th>
	 		<th><?php echo $language->lang_echo('TITLE') ?></th>
	 		<th><?php echo $language->lang_echo('OPEN_TICKETS') ?></th>
	 		<th><?php echo $language->lang_echo('HOUR_BUDGET') ?></th>
	 	</tr>
	 </thead>
	 <tbody>
	 <?php foreach($this->get('clientProjects') as $project): ?>
		<?php if(isset($project['id']) && $project['id'] > 0): ?>
			<tr>
				<td><?php echo $project['id'] ?></td>
				<td><?php echo $project['name'] ?></td>
				<td><?php echo $project['numberOfTickets'] ?></td>
				<td><?php echo $project['hourBudget'] ?></td>
			</tr>
		<?php endif; ?>
	 <?php endforeach; ?>
	 </tbody>
	</table>

	<!--
	<form action='' method='POST'>
		<select name='projects[]'>
			<option value=''></option>
		</select>
	</form>
	-->
</div>


</div>
</div>