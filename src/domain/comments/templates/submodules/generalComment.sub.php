<?php
$comments = new leantime\domain\repositories\comments();
$formUrl = CURRENT_URL;

//Controller may not redirect. Make sure delComment is only added once
if (strpos($formUrl, '?delComment=') !== false) {
    $urlParts = explode('?delComment=', $formUrl);
    $deleteUrlBase = $urlParts[0] . "?delComment=";
} else {
    $deleteUrlBase = $formUrl . "?delComment=";
}
?>

<h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl ?>"
      id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"
       style="display:none;" id="mainToggler"><span
                class="fa fa-plus-square"></span> <?php echo $this->__('links.add_new_comment') ?>
    </a>
    <div id="comment0" class="commentBox">
        <!--<img src="<?= BASE_URL ?>/api/users?profileImage=currentUser" style="float:left; width:50px; margin-right:10px; padding:2px;"/>-->
        <div class="commentImage">
            <img src="<?= BASE_URL ?>/api/users?profileImage=currentUser"/>
        </div>
        <div class="commentReply">

                <textarea rows="5" cols="50" class="tinymceSimple"
                          name="text"></textarea>
                <input type="submit" value="<?php echo $this->__('buttons.save') ?>"
                       name="comment" class="btn btn-primary btn-success"
                       style="margin-left: 0px;"/>
        </div>
        <input type="hidden" name="comment" value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        <br/>
    </div>

    <div id="comments">
        <div>
            <?php foreach ($this->get('comments') as $row) : ?>
                <div class="clearall">

                    <div class="commentImage">
                        <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $row['profileId'] ?>"/>
                    </div>

                    <div class="commentMain">
                        <div class="commentContent">
                            <div class="right commentDate">
                                <?php printf(
                                    $this->__('text.written_on'),
                                    $this->getFormattedDateString($row['date']),
                                    $this->getFormattedTimeString($row['date'])
                                ); ?>
                            </div>
                            <span class="name"><?php printf($this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></span>
                            <div class="text"><?php echo ($row['text']); ?></div>
                        </div>

                        <div class="commentLinks">
                            <a href="javascript:void(0);"
                               onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                                <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                            </a>

                            <?php if ($row['userId'] == $_SESSION['userdata']['id']) { ?>
                                <a href="<?php echo $deleteUrlBase . $row['id'] ?>"
                                   class="deleteComment">
                                    <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                </a>
                            <?php } ?>

                        </div>

                        <div class="replies">
                            <?php if ($comments->getReplies($row['id'])) : ?>
                                <?php foreach ($comments->getReplies($row['id']) as $comment) : ?>
                                    <div>

                                        <div class="commentImage">
                                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $comment['profileId'] ?>"/>
                                        </div>

                                        <div class="commentMain">
                                            <div class="commentContent">
                                                <div class="right commentDate">
                                                    <?php printf(
                                                        $this->__('text.written_on'),
                                                        $this->getFormattedDateString($comment['date']),
                                                        $this->getFormattedTimeString($comment['date'])
                                                    ); ?>
                                                </div>
                                                <span class="name"><?php printf($this->__('text.full_name'), $this->escape($comment['firstname']), $this->escape($comment['lastname'])); ?></span>
                                                <div class="text"><?php echo ($comment['text']); ?></div>
                                            </div>

                                            <div class="commentLinks">
                                                <?php if ($comment['userId'] == $_SESSION['userdata']['id']) { ?>
                                                    <a href="<?php echo $deleteUrlBase . $comment['id'] ?>"
                                                       class="deleteComment">
                                                        <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="clearall"></div>
                                    </div>

                                <?php endforeach; ?>
                            <?php endif; ?>

                            <div style="display:none;" id="comment<?php echo $row['id']; ?>" class="commentBox">
                                <div class="commentImage">
                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $_SESSION['userdata']['profileId'] ?>"/>
                                </div>
                                <div class="commentReply">

                                    <input type="submit"
                                           value="<?php echo $this->__('links.reply') ?>"
                                           name="comment" class="btn btn-default"/>
                                </div>
                                <div class="clearall"></div>
                            </div>

                        </div>


                    </div>

                </div>


            <?php endforeach; ?>
        </div>
    </div>

    <div class="clearall"></div>
</form>

<script type='text/javascript'>
    leantime.generalController.initSimpleEditor();

    function toggleCommentBoxes(id) {
        if (id == 0) {
            jQuery('#mainToggler').hide();
        } else {
            jQuery('#mainToggler').show();
        }
        jQuery('.commentBox textarea').remove();

        jQuery('.commentBox').hide('fast', function () {});

        jQuery('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
        leantime.generalController.initSimpleEditor();

        jQuery('#comment' + id + '').show('fast');
        jQuery('#father').val(id);
    }
</script>
