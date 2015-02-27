<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$project = $this->get('project');
?>


<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('CONFIRM_DELETE'); ?></h5>
                <h1><?php printf($language->lang_echo('DELETE_PROJECT'), $project['name']); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


<?php if($this->get('msg') !== '') { ?>
	<span class="info">
		<?php echo $lang[$this->get('msg')]; ?>
	</span>
<?php } ?>

<form method="post">
	<p><?php echo $lang['CONFIRM_DELETE_TEXT']; ?></p><br />
	<input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del" class="button" />
</form>


