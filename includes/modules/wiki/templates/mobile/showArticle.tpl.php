<script type="text/javascript">
	$(document).ready(function() 
    	{ $('#tabs').tabs();

        	$('#comments').pager('div');

        	toggleCommentBoxes(0);
    	} 
	);

function toggleCommentBoxes(id){
		
		$('.commentBox').hide('fast',function(){

			$('.commentBox textarea').remove(); 

			$('#comment'+id+'').prepend('<textarea rows="5" cols="30" name="text"></textarea>');
			
				
				
				
		}); 

		$('#comment'+id+'').show('slow');		

		
	} 
</script>



			<?php
			defined( 'RESTRICTED' ) or die( 'Restricted access' );
			$article = $this->get('article');
			$helper = $this->get('helper');
			

			?>

			<?php if($this->get('info') != ''){ ?>

<div class="fail"><span class="info"><?php echo $lang[$this->get('info')]; ?></span>
</div>

			<?php } ?>

<h1><?php echo ''.$lang['ARTICLE'].': '.$article['headline'].''; ?></h1>

<div id="tabs">
<ul>
	<li><a href="#articledetails"><?php echo$lang['ARTICLEDETAILS']; ?></a></li>
	<li><a href="#attachments"><?php echo$lang['ATTACHMENTS']; ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
	<li><a href="#commentList"><?php echo$lang['DISKUSSION']; ?> (<?php echo $this->get('numComments'); ?>)</a></li>
</ul>

<div id="articledetails">

<h1><?php echo $article['headline']; ?></h1>
<p><?php echo $helper->timestamp2date($article['date'], 2); ?> - <?php echo $article['firstname']; ?> <?php echo $article['lastname']; ?></p>
			
<br />
<p>
	<br />
	<?php echo $article['text']; ?><br />
	<br />
</p>
</div>



<div id="attachments"><?php 

$row = '';

foreach($this->get('files') as $row){?> <a
	href="userdata/<?php echo $row['encName']; ?>" target="_blank"><?php echo $row['realName']; ?></a><br />
<?php printf("<span class=\"grey\">".$lang['UPLOADED_BY_ON']."</span>", $row['firstname'], $row['lastname'], $helper->timestamp2date($row['date'], 2)); ?>

<?php if($this->get('role') === 'admin'){ ?> <br />| <a
	href="index.php?act=wiki.showArticle&amp;id=<?php echo $article['id']; ?>&amp;delFile=<?php echo $row['encName']; ?>"><?php echo $lang['DELETE']; ?></a>
<?php } ?> <br />
<hr />
<br />
<?php } ?> <?php if(count($this->get('files')) == 0){ ?>
<?php echo $lang['ERROR_NO_FILES']; ?> <?php } ?></div>

<div id="commentList">

<form method="post" accept-charset="utf-8"
	action="index.php?act=wiki.showArticle&amp;id=<?php echo $article['id']; ?>#commentList">
<a href="javascript:void(0);" onclick="toggleCommentBoxes(0)">Kommentieren</a>	
<br /><br />
	<span style="display:none;" id="comment0" class="commentBox">
		<textarea rows="5" cols="30" name="text"></textarea><br />
		<input type="submit" value="<?php echo $lang['SUBMIT']; ?>"
			name="comment" class="button" />
			<input type="hidden" name="father" id="father"/>
		
		<br />
</span>
<hr />


<div id="comments">
<div><?php 
$i = 1;
$k = 1;
$oldCommentParent = '';

$openSpan = 0;

foreach($this->get('comments') as $row){?>


	
	<?php 
	
		$tabs = $row['level'] * 20; 
		
		
		
	?>
	
	<span style="display:block; padding-left:10px; margin-left:<?php echo $tabs;?>px; <?php if($tabs > 1) echo'background:#e1e1e1;'?> border-bottom:1px solid #fff;">
	
		<br />
	

		<p><?php echo nl2br($row['text']); ?></p>
		<br />
		
		<?php printf("<small class=\"grey\">".$lang['WRITTEN_ON_BY']."</small>", $helper->timestamp2date($row['date'], 2), $helper->timestamp2date($row['date'], 1), $row['firstname'], $row['lastname']); ?>
		
		<?php if($this->get('role') === 'admin'){ ?> <br /><a
			href="index.php?act=wiki.showArticle&amp;id=<?php echo $article['id']; ?>&amp;delComment=<?php echo $row['id']; ?>#commentList"><?php echo $lang['DELETE']; ?></a>
		<?php } ?>
		
		| <a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $k; ?>)">Kommentieren</a>
		<br /><br /><hr />
		<span style="display:none;" id="comment<?php echo$k;?>" class="commentBox">
			<textarea rows="5" cols="50" name="text"></textarea><br />
			<input type="submit" value="<?php echo $lang['SUBMIT']; ?>"
				name="comment" class="button" onclick="$('#father').val('<?php echo $row['id']; ?>')" />
		</span>
		
		<br/>
		
		
	</span>

	
	
		
		
	<?php $oldCommentParent = $row['commentParent']; ?>
	
	<?php if($i == '5'){ ?></div>
	<div><?php $i=0;
	}

	$i++;
	$k++;
}

if(count($this->get('comments')) == 0){?> <?php echo $lang['ERROR_NO_COMMENTS']; ?>
<?php } ?></div>

<br /><br />

</div>

</div>


</div>
<br />
<?php if($this->get('role') === 'admin' || $_SESSION['userdata']['id'] == $row['authorId']){ ?>
	<a href="index.php?act=wiki.editArticle&id=<?php echo $article['id']; ?>" class="link"><?php echo $lang['EDIT']; ?></a>
	<?php } ?> 
	
	<?php if($this->get('role') === 'admin'){ ?><a
	href="index.php?act=wiki.delArticle&id=<?php echo $article['id']; ?>" class="link"><?php echo $lang['DELETE']; ?></a>
	<?php } ?>
	
	<a href="index.php?act=wiki.showAll" class="link"><?php echo $lang['BACK']; ?></a>
