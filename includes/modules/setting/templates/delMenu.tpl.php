<?php 
defined( 'RESTRICTED' ) or die( 'Restricted access' );

?>
<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('MENU_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('EDIT_MENU'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

			<?php echo $this->displayNotification() ?>


	<form method="post" accept-charset="utf-8" class="stdform">
		<div class="widget">
			<h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
			<div class="widgetcontent">

				<p><?php echo $language->lang_echo('CONFIRM_DELETE') ?></p>
				<p class="stdformbutton">
					<input type="submit" value="<?php echo $language->lang_echo('DELETE') ?>" name="del" class="button" />
				</p>
			</div>
		</div>
	</form>
