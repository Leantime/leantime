<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );


$helper = $this->get('helper');

?>
<script type="text/javascript">

	$(document).ready(function() 
    	{ 
		    		
        	
			$('#articles').pager('div', {navAttach: 'prepend'});
        	
        	
    	} 
	); 
    
</script>

<h1><?php echo $lang['WIKI']; ?></h1>

<div id="loader">&nbsp;</div>

<form action="index.php?act=wiki.showAll" method="post">

<fieldset class="left"><legend><?php echo $lang['OVERVIEW']; ?></legend>
<div id="articles">
	<hr/>
	<br /><br />
	<div>
		<?php
		$i=1;
		foreach($this->get('allArticles') as $row) {?>
			<p><?php echo $helper->timestamp2date($row['date'], 2); ?> - <?php echo $row['lastname']; ?> <?php echo $row['firstname']; ?></p>
			<h1><?php echo $row['headline']; ?></h1>
			<p><?php echo$helper->text_split($row['text']); ?><br /><br /></p>
			<a href="index.php?act=wiki.showArticle&amp;id=<?php echo $row['id']?>"><?php echo $lang['MORE']; ?></a>
			<p style="clear:both;">&nbsp;</p><br /><br /><hr /><br /><br />	
			
			<?php if($i == '5'){ ?></div>
			<div><?php $i=0;
			}
			
			$i++;
			}
			
			if(count($this->get('allArticles')) < 1){
			echo $lang['NO_ENTRIES'];
			}
		 	?>
	</div>

</div>
</fieldset>

<fieldset><legend><?php echo $lang['EDIT']; ?></legend> 
<p>
<a href="index.php?act=wiki.newArticle"><?php echo $lang['NEW_ARTICLE']; ?></a><br />
<a href="index.php?act=wiki.newCategory"><?php echo $lang['NEW_CATEGORY']; ?></a>
</p>
</fieldset>
<br />

<fieldset><legend><?php echo $lang['SEARCHFORM']; ?></legend> <label
	for="term"><?php echo $lang['SEARCHTERM']; ?></label> <input
	type="text" name="term" id="term"
	value="<?php echo $this->get('term'); ?>" /> <br />
<input type="submit" class="button"
	value="<?php echo $lang['SEARCH']; ?>" id="search" name="search" /> <?php echo $this->get('numText'); ?>

</fieldset>

<fieldset>
<legend><?php echo $lang['CATEGORIES']; ?></legend>
	<p>
	<?php foreach($this->get('categories') as $row) {?>
	<a href="index.php?act=wiki.showAll&amp;catId=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a><br />
	<?php } ?>
	</p>
</fieldset>

<fieldset>
<legend>Tags</legend>
	<p>
	
	<?php echo $this->get('tagCloud'); ?>
	</p>
</fieldset>
</form>
