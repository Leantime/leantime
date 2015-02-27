<?php

$helper = new helper();
$comments = new comments();
$language = new language();
$language->setModule('tickets');
$language->readIni();

?>


<script type='text/javascript'>

	function toggleCommentBoxes(id){
		
		jQuery('.commentBox').hide('fast',function(){

			jQuery('.commentBox textarea').remove(); 

			jQuery('#comment'+id+'').prepend('<textarea rows="5" cols="75" name="text"></textarea>');
			
		}); 

		jQuery('#comment'+id+'').show('fast');			
		
		jQuery('#father').val(id);	
	}	

</script>

<form method="post" accept-charset="utf-8" action="#comment" id="commentForm">
	<a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"><?php echo $language->lang_echo('COMMENT') ?></a>
	<div id="comment0" class="commentBox">
		
		<textarea rows="5" cols="50" name="text"></textarea><br />
		
		<input type="submit" value="<?php echo $language->lang_echo('SUBMIT'); ?>" name="comment" class="button" />

		<input type="hidden" name="father" id="father" value="0"/>
		
		<br />
	</div>
	<hr />


<div id="comments">
 <div>

<?php foreach($this->get('comments') as $row): ?>
	
	<div style="display:block; padding:10px; margin-bottom:10px; border-bottom:1px solid #d9d9d9;">
	 	<?php 
			 	$files = new files();
				$file = $files->getFile($row['profileId']);
				
				$img = '/includes/modules/general/templates/img/default-user.png';
				if ($file && file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/user/'.$file['encName'].'.'.$file['extension']))
					$img = '/userdata/user/'.$file['encName'].'.'.$file['extension'];
					
				?>	
				
				<img src="<?php echo $img; ?>" style="float:left; width:75px; margin-right:10px; padding:2px; border:1px solid #ddd;"/>
				<strong><?php echo $row['firstname']; ?> <?php echo $row['lastname']; ?></strong><br />
				<p><?php echo nl2br($row['text']); ?></p>
				<div class="clear"></div>
		<small>
			<?php printf($language->lang_echo('WRITTEN_ON_BY'), 
						$helper->timestamp2date($row['date'], 2), 
						$helper->timestamp2date($row['date'], 1), 
						$row['firstname'], 
						$row['lastname']); ?>
		</small>

		| <a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
			<?php echo $language->lang_echo('COMMENT') ?>
		</a>
		
		<div style="display:none;" id="comment<?php echo $row['id'];?>" class="commentBox">

			<br/><input type="submit" value="<?php echo $language->lang_echo('SUBMIT'); ?>" name="comment" class="button" />
		</div>
		
	</div>
	
	<?php if ($comments->getReplies($row['id'])): ?>
		<?php foreach($comments->getReplies($row['id']) as $comment): ?>
			
			<div style="display:block; padding:10px; margin-left: 20px; border-bottom:1px solid #d9d9d9; background:#eee;">
			 	<?php 
			 	$files = new files();
				$file = $files->getFile($comment['profileId']);
				
				$img = '/includes/modules/general/templates/img/default-user.png';
				if ($file && file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/user/'.$file['encName'].'.'.$file['extension']))
					$img = '/userdata/user/'.$file['encName'].'.'.$file['extension'];
					
				?>	
				
				<img src="<?php echo $img; ?>" style="float:left; width:75px; margin-right:10px; padding:2px; border:1px solid #ddd;"/>
				<strong><?php echo $comment['firstname']; ?> <?php echo $comment['lastname']; ?></strong><br />
				<p><?php echo nl2br($comment['text']); ?></p>
				<div class="clear"></div>
				<small>
					<?php printf($language->lang_echo('WRITTEN_ON_BY'), 
									$helper->timestamp2date($comment['date'], 2), 
									$helper->timestamp2date($comment['date'], 1), 
									$comment['firstname'], 
									$comment['lastname']); ?>
				</small>
				
			</div>
			
		<?php endforeach; ?>
	<?php endif; ?>
	
<?php endforeach; ?>

<?php if(count($this->get('comments')) == 0){?> 
	<?php echo $language->lang_echo('ERROR_NO_COMMENTS'); ?>
<?php } ?>

</form>
 </div><br /><br />
</div>
