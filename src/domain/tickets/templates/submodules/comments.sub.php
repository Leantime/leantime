<?php

$helper = new helper();
$ticket = $this->get('ticket');
?>
<a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"><?php echo $language->lang_echo('COMMENT') ?></a>	

<form method="post" accept-charset="utf-8" action="<?=BASE_URL ?>/index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>#comment">

	<br /><br />

	<span id="comment0" class="commentBox">
		
		<textarea rows="5" cols="50" name="text" name="text"></textarea><br />
		
		<input type="submit" value="<?php echo $language->lang_echo('SUBMIT'); ?>" name="comment" class="button" />
		<input type="hidden" name="father" id="father"/>
		
		<br />
	</span>
<hr />


<div id="comments">
<div>
<?php 

$i = 1;
$k = 1;
$oldCommentParent = '';
$openSpan = 0;
?>

<?php foreach($this->get('comments') as $row) { ?>
	
	<?php $tabs = $row['level'] * 20; ?>
	
	<span style="display:block; padding-left:10px; margin-left:<?php echo $tabs;?>px; <?php if($tabs > 1) echo'background:#e1e1e1;'?> border-bottom:1px solid #fff;">
	 	<?php 
	 	$files = new files();
		$file = $files->getFile($value['profileId']);
		
		$img = '/includes/modules/general/templates/img/default-user.png';
		if ($file)
			$img = BASE_URL."/download.php?module=".$file['module'] ."&encName=".$file['encName']."&ext=".$file['extension']."&realName=".$file['realName'];
			
		?>	
		<br />
		<img src="<?php echo $img; ?>" style="float:left; width:100px; margin-right:10px;"/>
		<br /><p><?php echo nl2br($row['text']); ?></p><br />
		<div class="clear"></div>
		<?php printf("<small class=\"grey\">".$language->lang_echo('WRITTEN_ON_BY')."</small>", $helper->timestamp2date($row['date'], 2), $helper->timestamp2date($row['date'], 1), $row['firstname'], $row['lastname']); ?>

		
		<?php if($this->get('role') === 'admin'){ ?> | 
			<a href="<?=BASE_URL?>/index.php?act=tickets.showTicket&amp;id=<?php echo $ticket['id']; ?>&amp;delComment=<?php echo $row['id']; ?>#commentList">
				<?php echo $language->lang_echo('DELETE'); ?>
			</a>
		<?php } ?>
		
		|<a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $k; ?>)"><?php echo $language->lang_echo('Reply', false) ?></a>
		<br /><br /><hr />
		<span style="display:none;" id="comment<?php echo$k;?>" class="commentBox">
			<textarea rows="5" cols="50" name="text"></textarea><br />
			<input type="submit" value="<?php echo $language->lang_echo('SUBMIT'); ?>" name="comment" class="button" onclick="$('#father').val('<?php echo $row['id']; ?>')" />
		</span>
		
		<br/>
		
		
	</span>
	
	<?php $oldCommentParent = $row['commentParent']; ?>
	
	<?php if($i == '5'){ ?>
		
	</div>
	<div>
	<?php $i=0;
	}

	$i++;
	$k++;
} ?>

<?php if(count($this->get('comments')) == 0){?> 
	<?php echo $language->lang_echo('ERROR_NO_COMMENTS'); ?>
<?php } ?>

</div><br /><br />
</div>
</form>