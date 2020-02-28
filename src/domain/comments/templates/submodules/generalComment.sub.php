<?php

    $comments = new leantime\domain\repositories\comments();

    $formUrl = $this->get('formUrl');
    $deleteUrlBase = "";

    if($formUrl == "") {
        $formUrl = "#comments";
        $deleteUrlBase = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&delComment=";
    }else{
        $deleteUrlBase = $formUrl."&delComment=";
    }

?>

<h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl?>" id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)" style="display:none;" id="mainToggler"><span class="fa fa-plus-square"></span> <?php echo $this->__('links.add_new_comment') ?></a>
    <div id="comment0" class="commentBox">

        <img src="/api/users?profileImage=currentUser" style="float:left; width:50px; margin-right:10px; padding:2px;"/>
        <textarea rows="5" cols="50" name="text"></textarea><br />
        
        <input type="submit" value="<?php echo $this->__('buttons.save') ?>" name="comment" class="button" style="margin-left: 60px;"/>
        <input type="hidden" name="comment"  value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        
        <br />
    </div>
    <hr />

    <div id="comments">
        <div>

            <?php foreach($this->get('comments') as $row): ?>

                <div style="display:block; padding:10px; margin-top:10px; border-bottom:1px solid #f0f0f0;">

                            <img src="/api/users?profileImage=<?=$row['profileId']?>" style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                            <div class="right"><?php printf(
                                    $this->__('text.written_on'),
                                    date($this->__('language.dateformat'), strtotime($row['date'])),
                                    date($this->__('language.timeformat'), strtotime($row['date']))
                                ); ?></div>
                            <strong><?php $this->e($row['firstname']); ?> <?php $this->e($row['lastname']); ?></strong><br />
                            <p><?php echo nl2br($this->escape($row['text'])); ?></p>
                            <div class="clear"></div>

                    <div style="padding-left:60px">
                        <a href="javascript:void(0);" onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                            <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                        </a>

                        <?php if($row['userId'] == $_SESSION['userdata']['id']) { ?>
                        |
                            <a href="<?php echo $deleteUrlBase.$row['id'] ?>#comments" class="deleteComment">
                                <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                            </a>
                        <?php } ?>
                        <div style="display:none;" id="comment<?php echo $row['id'];?>" class="commentBox">
                            <br/><input type="submit" value="<?php echo $this->__('links.reply') ?>" name="comment" class="button" />
                        </div>
                    </div>

                </div>

                <?php if ($comments->getReplies($row['id'])) : ?>
                    <?php foreach($comments->getReplies($row['id']) as $comment): ?>

                        <div style="display:block; padding:10px; padding-left: 60px; border-bottom:1px solid #f0f0f0;">


                            <img src="/api/users?profileImage=<?=$row['profileId']?>" style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                            <div class="right"><?php printf(
                                    $this->__('text.written_on'),
                                    date($this->__('language.dateformat'), strtotime($row['date'])),
                                    date($this->__('language.timeformat'), strtotime($row['date']))
                                ); ?></div>
                            <strong><?php $this->e($comment['firstname']); ?> <?php $this->e($comment['lastname']); ?></strong><br />
                            <p><?php echo nl2br($this->escape($comment['text'])); ?></p>
                            <div class="clear"></div>

                            <div style="padding-left:60px">
                            <?php if($comment['userId'] == $_SESSION['userdata']['id']) { ?>

                                <a href="<?php echo $deleteUrlBase.$comment['id'] ?>#comments" class="deleteComment">
                                    <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                </a>

                            <?php } ?>
                            </div>

                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>
    </div>

</form>

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