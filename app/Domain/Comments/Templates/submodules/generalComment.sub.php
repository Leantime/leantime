<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$comments = app()->make(Leantime\Domain\Comments\Repositories\Comments::class);
$formUrl = CURRENT_URL;
$formHash = md5($formUrl);

//Controller may not redirect. Make sure delComment is only added once
if (str_contains($formUrl, '?delComment=')) {
    $urlParts = explode('?delComment=', $formUrl);
    $deleteUrlBase = $urlParts[0] . "?delComment=";
} else {
    $deleteUrlBase = $formUrl . "?delComment=";
}
?>

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl ?>" id="commentForm-<?=$formHash ?>" class="formModal">

    <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
        <div class="mainToggler-<?=$formHash ?>" id="">
            <div class="commentImage">
                <img src="<?= BASE_URL ?>/api/users?profileImage=<?=session("userdata.id") ?>&v=<?=format(session("userdata.modified"))->timestamp() ?>" />
            </div>
            <div class="commentReply inactive">
                <a href="javascript:void(0);" onclick="toggleCommentBoxes(0, '<?=$formHash?>')">
                    <?php echo $tpl->__('links.add_new_comment') ?>
                </a>
            </div>
        </div>

        <div id="comment-<?=$formHash ?>-0" class="commentBox-<?=$formHash ?> commenterFields" style="display:none;">
            <div class="commentImage">
                <img src="<?= BASE_URL ?>/api/users?profileImage=<?=session("userdata.id")?>&v=<?=format(session("userdata.modified"))->timestamp() ?>" />
            </div>
            <div class="commentReply">
                <textarea rows="5" cols="50" class="tinymceSimple" name="text"></textarea>
                <input type="submit" value="<?php echo $tpl->__('buttons.save') ?>" name="comment" class="btn btn-primary btn-success" style="margin-left: 0px;"/>
            </div>
            <input type="hidden" name="comment" class="commenterField" value="1"/>
            <input type="hidden" name="father" class="commenterField" id="father-<?=$formHash ?>" value="0"/>
            <br/>
        </div>
    <?php } ?>

    <div id="comments-<?=$formHash ?>">
        <div>
            <?php foreach ($tpl->get('comments') as $row) : ?>
                <div class="clearall">
                    <div class="commentImage">
                        <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $row['userId'] ?>&v=<?= format($row['userModified'])->timestamp() ?>"/>
                    </div>
                    <div class="commentMain">
                        <div class="commentContent">
                            <div class="right commentDate">
                                <?php printf(
                                    $tpl->__('text.written_on'),
                                    format($row['date'])->date(),
                                    format($row['date'])->time()
                                ); ?>
                                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                        <div class="inlineDropDownContainer" style="float:right; margin-left:10px;">
                                            <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </a>

                                            <ul class="dropdown-menu">
                                                <?php if (($row['userId'] == session("userdata.id")) || $login::userIsAtLeast($roles::$manager)) { ?>
                                                    <li><a href="<?php echo $deleteUrlBase . $row['id'] ?>" class="deleteComment formModal">
                                                        <span class="fa fa-trash"></span> <?php echo $tpl->__('links.delete') ?>
                                                    </a></li>
                                                <?php } ?>
                                                <?php
                                                if (isset($tpl->get('ticket')->id)) {?>
                                                        <li><a href="javascript:void(0);" onclick="leantime.ticketsController.addCommentTimesheetContent(<?=$row['id'] ?>, <?=$tpl->get('ticket')->id ?>);"><?=$tpl->__("links.add_to_timesheets"); ?></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                            </div>
                            <span class="name"><?php printf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></span>
                            <div class="text mce-content-body" id="commentText-<?=$formHash ?>-<?=$row['id']?>">
                                <?php echo $tpl->escapeMinimal($row['text']); ?>
                            </div>


                        </div>

                        <div class="commentLinks">
                            <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
                                <a href="javascript:void(0);"
                                   onclick="toggleCommentBoxes(<?php echo $row['id']; ?>, '<?=$formHash ?>')">
                                    <span class="fa fa-reply"></span> <?php echo $tpl->__('links.reply') ?>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="replies">
                            <?php if ($comments->getReplies($row['id'])) : ?>
                                <?php foreach ($comments->getReplies($row['id']) as $comment) : ?>
                                    <div>
                                        <div class="commentImage">
                                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $comment['userId'] ?>&v=<?=format($comment['userModified'])->timestamp() ?>"/>
                                        </div>
                                        <div class="commentMain">
                                            <div class="commentContent">
                                                <div class="right commentDate">
                                                    <?php printf(
                                                        $tpl->__('text.written_on'),
                                                        format($comment['date'])->date(),
                                                        format($comment['date'])->time()
                                                    ); ?>
                                                </div>
                                                <span class="name"><?php printf($tpl->__('text.full_name'), $tpl->escape($comment['firstname']), $tpl->escape($comment['lastname'])); ?></span>
                                                <div class="text mce-content-body"><?php echo $tpl->escapeMinimal($comment['text']); ?></div>
                                            </div>

                                            <div class="commentLinks">
                                                <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
                                                    <a href="javascript:void(0);"
                                                       onclick="toggleCommentBoxes(<?php echo $row['id']; ?>, '<?=$formHash ?>')">
                                                        <span class="fa fa-reply"></span> <?php echo $tpl->__('links.reply') ?>
                                                    </a>
                                                    <?php if ($comment['userId'] == session("userdata.id")) { ?>
                                                        <a href="<?php echo $deleteUrlBase . $comment['id'] ?>"
                                                           class="deleteComment formModal">
                                                            <span class="fa fa-trash"></span> <?php echo $tpl->__('links.delete') ?>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="clearall"></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div style="display:none;" id="comment-<?=$formHash?>-<?php echo $row['id']; ?>" class="commentBox">
                                <div class="commentImage">
                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= session("userdata.id") ?>&v=<?= format(session("userdata.modified"))->timestamp() ?>"/>
                                </div>
                                <div class="commentReply">
                                    <input type="submit" value="<?php echo $tpl->__('links.reply') ?>" name="comment" class="btn btn-primary"/>
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

    leantime.editorController.initSimpleEditor();

    function toggleCommentBoxes(id, formHash) {

        <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
            if (id == 0) {
                jQuery('.mainToggler-'+formHash).hide();
            } else {
                jQuery('.mainToggler-'+formHash).show();
            }
            jQuery('.commentBox-'+formHash+' textarea').remove();

            jQuery('.commentBox-'+formHash+'').hide();

            jQuery('#comment-'+formHash+'-' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" id="editor_'+formHash+'" class="tinymceSimple"></textarea>');
            leantime.editorController.initSimpleEditor();

            setTimeout(function () {                         // you may not need the timeout
                tinyMCE.get('editor_'+formHash+'').focus();
            }, 50);

            jQuery('#comment-'+formHash+'-' + id + '').show();
            jQuery('#father-'+formHash).val(id);

        <?php } ?>
    }

    jQuery(".confetti").click(function(){
        confetti({
            spread: 70,
            origin: { y: 1.2 },
        });
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
