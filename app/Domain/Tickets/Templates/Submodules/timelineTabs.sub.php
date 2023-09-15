

<?php

function findActive($route)
{
    if (str_contains(\Leantime\Core\Frontcontroller::getCurrentRoute(), $route)) {
        return "active";
    }
    return "";
}

findActive("");
?>

<div class="maincontentinner tabs">
    <ul>
        <li class="<?=findActive('roadmap'); ?>">
            <a href="<?=BASE_URL ?>/tickets/roadmap<?=$tpl->get('searchParams') ?>">
                <?=$tpl->__("links.timeline") ?>
            </a>
        </li>
        <li class="<?=findActive('Calendar'); ?>">
            <a href="<?=BASE_URL ?>/tickets/showProjectCalendar<?=$tpl->get('searchParams') ?>">
                <?=$tpl->__("links.calendar") ?>
            </a>
        </li>
    </ul>
</div>
