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

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl ?>" id="commentForm">

    <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)" style="display:none;" id="mainToggler">
        <span class="fa fa-plus-square"></span> <?php echo $this->__('links.add_new_comment') ?>
    </a>
        <div id="comment0" class="commentBox">
            <div class="commentImage">
                <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$_SESSION['userdata']['id']?>" />
            </div>
            <div class="commentReply">
                <textarea rows="5" cols="50" class="tinymceSimple" name="text"></textarea>
                <input type="submit" value="<?php echo $this->__('buttons.save') ?>" name="comment" class="btn btn-primary btn-success" style="margin-left: 0px;"/>
            </div>
            <input type="hidden" name="comment" value="1"/>
            <input type="hidden" name="father" id="father" value="0"/>
            <br/>
        </div>
    <?php } ?>

    <div id="comments">
        <div>
            <?php foreach ($this->get('comments') as $row) : ?>
                <div class="clearall">
                    <div class="commentImage">
                        <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $row['userId'] ?>"/>
                    </div>
                    <div class="commentMain">
                        <div class="commentContent">
                            <div class="right commentDate">
                                <?php printf(
                                    $this->__('text.written_on'),
                                    $this->getFormattedDateString($row['date']),
                                    $this->getFormattedTimeString($row['date'])
                                ); ?>
                                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                        <div class="inlineDropDownContainer" style="float:right; margin-left:10px;">
                                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </a>

                                            <ul class="dropdown-menu">
                                                <?php if ($row['userId'] == $_SESSION['userdata']['id']) { ?>
                                                    <li><a href="<?php echo $deleteUrlBase . $row['id'] ?>" class="deleteComment">
                                                        <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                                    </a></li>
                                                <?php } ?>
                                                <?php
                                                if (isset($this->get('ticket')->id)) {?>
                                                        <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent(<?=$row['id'] ?>, <?=$this->get('ticket')->id ?>);"><?=$this->__("links.add_to_timesheets"); ?></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                            </div>
                            <span class="name"><?php printf($this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></span>
                            <div class="text" id="commentText-<?=$row['id']?>">
                                <?php echo $this->escapeMinimal($row['text']); ?>
                            </div>


                        </div>

                        <div class="commentLinks">
                            <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
                                <a href="javascript:void(0);"
                                   onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                                    <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="replies">
                            <?php if ($comments->getReplies($row['id'])) : ?>
                                <?php foreach ($comments->getReplies($row['id']) as $comment) : ?>
                                    <div>
                                        <div class="commentImage">
                                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $comment['userId'] ?>"/>
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
                                                <div class="text"><?php echo $this->escapeMinimal($comment['text']); ?></div>
                                            </div>

                                            <div class="commentLinks">
                                                <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
                                                    <a href="javascript:void(0);"
                                                       onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                                                        <span class="fa fa-reply"></span> <?php echo $this->__('links.reply') ?>
                                                    </a>
                                                    <?php if ($comment['userId'] == $_SESSION['userdata']['id']) { ?>
                                                        <a href="<?php echo $deleteUrlBase . $comment['id'] ?>"
                                                           class="deleteComment">
                                                            <span class="fa fa-trash"></span> <?php echo $this->__('links.delete') ?>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="clearall"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div style="display:none;" id="comment<?php echo $row['id']; ?>" class="commentBox">
                                <div class="commentImage">
                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $_SESSION['userdata']['id'] ?>"/>
                                </div>
                                <div class="commentReply">
                                    <input type="submit" value="<?php echo $this->__('links.reply') ?>" name="comment" class="btn btn-default"/>
                                </div>
                                <div class="clearall"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (count($this->get('comments')) == 0) { ?>
        <div style="padding-left:40px;">
            <?php echo $this->__('text.no_comments') ?>
        </div>
    <?php } ?>
    <div class="clearall"></div>
</form>

<script type='text/javascript'>

    leantime.generalController.initSimpleEditor();

    function toggleCommentBoxes(id) {

        <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
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

        <?php } ?>
    }

    jQuery(".confetti").click(function(){
        confetti.start();
    });

    function respondToVisibility(element, callback) {
        var options = {
            root: document.documentElement,
        };

        var observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                callback(entry.intersectionRatio > 0);
            });
        }, options);

        observer.observe(element);
    }
</script>
