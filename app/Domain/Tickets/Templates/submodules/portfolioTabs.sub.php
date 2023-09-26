
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
        <li class="<?=findActive('showMy'); ?>">
            <a href="<?=BASE_URL ?>/projects/showMy">
                <?=$tpl->__("menu.card") ?>
            </a>
        </li>
        <li class="<?=findActive('roadmapAll'); ?>">
            <a href="<?=BASE_URL ?>/tickets/roadmapAll">
                <?=$tpl->__("links.timeline") ?>
            </a>
        </li>
        <li class="<?=findActive('showAllMilestonesOverview'); ?>">
            <a href="<?=BASE_URL ?>/tickets/showAllMilestonesOverview">
                <?=$tpl->__("links.table") ?>
            </a>
        </li>
    </ul>
</div>
