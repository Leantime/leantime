<?php

?>


<div class="pageheader">
        <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
           	<input type="text" name="term" placeholder="To search type and hit enter..." />
       	</form>
            
        <div class="pageicon"><span class="iconfa-laptop"></span></div>
     	<div class="pagetitle">
            <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        	<h1><?php echo $language->lang_echo('SUBMODULE_RIGHTS'); ?></h1>
    	</div>
    </div><!--pageheader-->
        
  	<div class="maincontent">
      	<div class="maincontentinner">
            	
            <form action='' method='POST'>
            	
            <input type='submit' value='<?php echo $language->lang_echo('SAVE') ?>' name='save' />
            	
			<table class='table table-bordered'>
				<colgroup>
			      	  <col class="con0"/>
			      	  <col class="con1"/>
			      	  <col class="con0"/>
			      	  <col class="con1"/>
				</colgroup>
				<thead>
					<tr>
						<th>Module</th>
						<th>Alias</th>
						<th>Title</th>
						<th>Roles</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($this->get('submodules') as $submodule): ?>
					 <tr>
					 	<td><?php echo $submodule['module'] ?></td>
					 	<td><?php echo $submodule['alias'] ?></td>
						<td>
							<input type='text' value='<?php echo $submodule['title'] ?>' name='title-<?php echo $submodule['alias'] ?>' />
						</td>
						<td>
							<select name="roles-<?php echo $submodule['alias'] ?>[]" id="roles" size="10" multiple="multiple" style="width:300px;">
								<optgroup label="Users">
									<?php foreach($this->get('roles') as $role): ?>
										<option value="<?php echo $role['id'] ?>" 
											<?php if (in_array($role['id'], explode(',',$submodule['roleIds']))): ?>
												selected="selected"
											<?php endif; ?>
										><?php echo $role['roleDescription'] ?></option>
										
									<?php endforeach; ?>
								</optgroup>
							</select>
						</td>
					 </tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			</form>
		</div>
	</div>
</div>