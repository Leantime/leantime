

		<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('WIDGETS') ?></h5>
                <h1><?php echo $language->lang_echo('DASHBOARD') ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>

				<form action='' method='POST' class='stdform'>

				<div class="widget">
				   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
				   <div class="widgetcontent">
	
						<p>
						<label for=''><?php echo $language->lang_echo('TITLE') ?></label>
						<span class='field'>
						<input type='text' name='title' />
						</span></p>
						
						<p>
						<label for=''><?php echo $language->lang_echo('SUBMODULE') ?></label>
						<span class='field'>
						<select name='submoduleAlias'>
							<?php foreach($this->get('submodules') as $submodule): ?>
								<option value='<?php echo $submodule['alias'] ?>'>
									<?php echo $submodule['submodule'] ?>
								</option>
							<?php endforeach; ?>
						</select>
						</span></p>
						
						<p class='stdformbutton'>
							<input type='submit' name='save' value='<?php echo $language->lang_echo('SAVE') ?>' />
						</p>
					
					</div>
				</div>
					
				</form>

			</div>
		</div>