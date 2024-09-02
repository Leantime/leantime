@extends($layout)

@section('content')

<?php
$comments = app()->make(Leantime\Domain\Comments\Repositories\Comments::class);
$formUrl = CURRENT_URL;

//Controller may not redirect. Make sure delComment is only added once
if (str_contains($formUrl, '?delComment=')) {
    $urlParts = explode('?delComment=', $formUrl);
    $deleteUrlBase = $urlParts[0] . "?delComment=";
} else {
    $deleteUrlBase = $formUrl . "?delComment=";
}
?>

<h4 class="widgettitle title-light"><span
            class="fa fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?>
</h4>

<form method="post" accept-charset="utf-8" action="<?php echo $formUrl ?>"
      id="commentForm">
    <a href="javascript:void(0);" onclick="toggleCommentBoxes(0)"
       style="display:none;" id="mainToggler"><span
                class="fa fa-plus-square"></span> <?php echo $tpl->__('links.add_new_comment') ?>
    </a>

    <div id="comment0" class="commentBox">
        <textarea rows="5" cols="50" class="tinymceSimple"
                  name="text"></textarea><br/>
        <input type="submit" value="<?php echo $tpl->__('buttons.save') ?>"
               name="comment" class="btn btn-default btn-success"
               style="margin-left: 0px;"/>
        <input type="hidden" name="comment" value="1"/>
        <input type="hidden" name="father" id="father" value="0"/>
        <br/>
    </div>
    <hr/>

    <div id="comments">
        <div>
            <?php foreach ($tpl->get('comments') as $row) : ?>
                <div style="display:block; padding:10px; margin-top:10px; border-bottom:1px solid #f0f0f0;">
                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $row['userId'] ?>&v=<?=format($row['userModified'])->timestamp() ?>"
                         style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                    <div class="right"><?php printf(
                        $tpl->__('text.written_on'),
                        format($row['date'])->date(),
                        format($row['date'])->time()
                                       ); ?></div>
                    <strong>
                    <?php printf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?>
                    </strong><br/>
                    <div style="margin-left:60px;"><?php echo($row['text']); ?></div>
                    <div class="clear"></div>
                    <div style="padding-left:60px">
                        <a href="javascript:void(0);" class="replyButton"
                           onclick="toggleCommentBoxes(<?php echo $row['id']; ?>)">
                            <span class="fa fa-reply"></span> <?php echo $tpl->__('links.reply') ?>
                        </a>

                        <?php if ($row['userId'] == session("userdata.id")) { ?>
                            |
                            <a href="<?php echo $deleteUrlBase . $row['id'] ?>"
                               class="deleteComment">
                                <span class="fa fa-trash"></span> <?php echo $tpl->__('links.delete') ?>
                            </a>
                        <?php } ?>
                        <div style="display:none;"
                             id="comment<?php echo $row['id']; ?>"
                             class="commentBox">
                            <br/><input type="submit"
                                        value="<?php echo $tpl->__('links.reply') ?>"
                                        name="comment" class="btn btn-default"/>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>

                <?php if ($comments->getReplies($row['id'])) : ?>
                    <?php foreach ($comments->getReplies($row['id']) as $comment) : ?>
                        <div style="display:block; padding:10px; padding-left: 60px; border-bottom:1px solid #f0f0f0;">
                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $comment['userId'] ?>&v=<?= $comment['userModified'] ?>"
                                 style="float:left; width:50px; margin-right:10px; padding:2px;"/>
                            <div>
                                <div class="right">
                                    <?php printf(
                                        $tpl->__('text.written_on'),
                                        format($comment['date'])->date(),
                                        format($comment['date'])->time()
                                    ); ?>
                                </div>
                                <strong>
                                <?php printf($tpl->__('text.full_name'), $tpl->escape($comment['firstname']), $tpl->escape($comment['lastname'])); ?>
                                </strong><br/>
                                <p style="margin-left:60px;"><?php echo nl2br($comment['text']); ?></p>
                                <div class="clear"></div>

                                <div style="padding-left:60px">
                                    <?php if ($comment['userId'] == session("userdata.id")) { ?>
                                        <a href="<?php echo $deleteUrlBase . $comment['id'] ?>"
                                           class="deleteComment">
                                            <span class="fa fa-trash"></span> <?php echo $tpl->__('links.delete') ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (count($tpl->get('comments')) == 0) { ?>
            <div class="text-center">
                <div style='width:33%' class='svgContainer'>
                    <?php echo file_get_contents(ROOT . "/dist/images/svg/undraw_real_time_collaboration_c62i.svg"); ?>
                    <?php $tpl->e($language->__('text.no_comments')) ?>
                </div>
            </div>
        <?php } ?>
    </div>
</form>

<script type='text/javascript'>


    leantime.editorController.initSimpleEditor();

    function toggleCommentBoxes(id) {
        <?php if ($login::userIsAtLeast($roles::$commenter)) { ?>
            if (id == 0) {
                jQuery('#mainToggler').hide();
            } else {
                jQuery('#mainToggler').show();
            }
            jQuery('.commentBox').hide('fast', function () {
                jQuery('.commentBox textarea').remove();
                jQuery('#comment' + id + '').prepend('<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
                leantime.editorController.initSimpleEditor();
            });

            jQuery('#comment' + id + '').show('fast');
            jQuery('#father').val(id);
        <?php } ?>
    }




</script>
