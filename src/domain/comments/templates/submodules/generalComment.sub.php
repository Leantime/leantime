<?php

$helper = $this->get('helper');
$comments = new leantime\domain\repositories\comments();
$language->setModule('tickets');
$language->readIni();

$formUrl = CURRENT_URL;

//Controller may not redirect. Make sure delComment is only added once
if (strpos($formUrl, '?delComment=') !== false) {
    $urlParts = explode('?delComment=', $formUrl);
    $deleteUrlBase = $urlParts[0]."?delComment=";
}else{
    $deleteUrlBase = $formUrl."?delComment=";
}

?>

<h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $language->lang_echo('Discussion', false); ?></h4>

<script type='text/javascript'>

    function toggleCommentBoxes(id){

        if(id==0){
            jQuery('#mainToggler').hide();
        }else{
            jQuery('#mainToggler').show();
        }

        jQuery('.commentBox').hide('fast',function(){

            jQuery('.commentBox textarea').remove(); 

            jQuery('#comment'+id+'').prepend('<textarea rows="5" cols="75" name="text"></textarea>');
            
        }); 

        jQuery('#comment'+id+'').show('fast');


        
        jQuery('#father').val(id);    
    }



</script>

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl?>" id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)" style="display:none;" id="mainToggler"><span class="fa fa-plus-square"></span> <?php echo $language->lang_echo('Add a new comment', false) ?></a>
    <div id="comment0" class="commentBox">
        
        <textarea rows="5" cols="50" name="text"></textarea><br />
        
        <input type="submit" value="Save" name="comment" class="button" />
        <input type="hidden" name="comment"  value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        
        <br />
    </div>
    <hr />

    <div id="comments">
        <div>

            <?php foreach($this->get('comments') as $row): ?>

                <div style="display:block; padding:10px; margin-bottom:10px; border-bottom:1px solid #d9d9d9;">
                    <?php
                            $files = new leantime\domain\repositories\files;
                            $file = $files->getFile($row['profileId']);

                            $img = BASE_URL.'/images/default-user.png';
                    if ($file) {
                        $img = BASE_URL."/download.php?module=".$file['module'] ."&encName=".$file['encName']."&ext=".$file['extension']."&realName=".$file['realName'];
                    }
                    ?>

                            <img src="<?php echo $img; ?>" style="float:left; width:75px; margin-right:10px; padding:2px;"/>
                            <strong><?php $this->e($row['firstname']); ?> <?php $this->e($row['lastname']); ?></strong><br />
                            <p><?php echo nl2br($this->escape($row['text'])); ?></p>
                            <div class="clear"></div>
                    <small>
                        <?php printf(
                            $language->lang_echo('WRITTEN_ON_BY'),
                            $helper->timestamp2date($row['date'], 2),
                            $helper->timestamp2date($row['date'], 1),
                            $this->escape($row['firstname']),
                            $this->escape($row['lastname'])
                        ); ?>
                    </small>

                    | <a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                        <span class="fa fa-reply"></span> Reply
                    </a>

                    <?php if($row['userId'] == $_SESSION['userdata']['id']) { ?>
                    |
                        <a href="<?php echo $deleteUrlBase.$row['id'] ?>" class="deleteComment">
                            <span class="fa fa-trash"></span> <?php echo $language->lang_echo('Delete', false) ?>
                        </a>
                    <?php } ?>
                    <div style="display:none;" id="comment<?php echo $row['id'];?>" class="commentBox">
                        <br/><input type="submit" value="Reply" name="comment" class="button" />
                    </div>

                </div>

                <?php if ($comments->getReplies($row['id'])) : ?>
                    <?php foreach($comments->getReplies($row['id']) as $comment): ?>

                        <div style="display:block; padding:10px; margin-left: 20px; border-bottom:1px solid #d9d9d9; background:#eee;">
                            <?php
                            $files = new leantime\domain\repositories\files;
                            $file = $files->getFile($comment['profileId']);

                            $img = BASE_URL.'/images/default-user.png';
                            if ($file) {

                                $img = BASE_URL."/download.php?module=".$file['module'] ."&encName=".$file['encName']."&ext=".$file['extension']."&realName=".$file['realName'];
                            }
                            ?>

                            <img src="<?php echo $img; ?>" style="float:left; width:75px; margin-right:10px; padding:2px;"/>
                            <strong><?php $this->e($comment['firstname']); ?> <?php $this->e($comment['lastname']); ?></strong><br />
                            <p><?php echo nl2br($this->escape($comment['text'])); ?></p>
                            <div class="clear"></div>
                            <small>
                                <?php printf(
                                    $language->lang_echo('WRITTEN_ON_BY'),
                                    $helper->timestamp2date($comment['date'], 2),
                                    $helper->timestamp2date($comment['date'], 1),
                                    $this->escape($comment['firstname']),
                                    $this->escape($comment['lastname'])
                                ); ?>
                            </small>

                            <?php if($comment['userId'] == $_SESSION['userdata']['id']) { ?>
                                |
                                <a href="<?php echo $deleteUrlBase.$comment['id'] ?>" class="deleteComment">
                                    <span class="fa fa-trash"></span> <?php echo $language->lang_echo('Delete', false) ?>
                                </a>
                            <?php } ?>

                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>

            <?php endforeach; ?>

            <?php if(count($this->get('comments')) == 0) {?>
                Nothing so far.
            <?php } ?>

        </div>
        <br />
        <br />
    </div>

</form>
