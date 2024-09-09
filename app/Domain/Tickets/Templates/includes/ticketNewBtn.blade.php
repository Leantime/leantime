<?php
$newField        = $tpl->get('newField');
?>
<?php if ($login::userIsAtLeast($roles::$editor) && !empty($newField)) { ?>
    <div class="btn-group pull-left" style="margin-right:5px;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown"><?=$tpl->__("links.new_with_icon") ?> <span class="caret"></span></button>
        <ul class="dropdown-menu">
            <?php foreach ($newField as $option) { ?>
                <li>
                    <a
                        href="<?= !empty($option['url']) ? $option['url'] : '' ?>"
                        class="<?= !empty($option['class']) ? $option['class'] : '' ?>"
                    > <?= !empty($option['text']) ? $tpl->__($option['text']) : '' ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
