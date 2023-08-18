
<?php

function findActive($route) {


    if(str_contains(\leantime\core\frontcontroller::getCurrentRoute(), $route)) {

        return "active";
    }

    return "";
}

findActive("");
?>

<div class="maincontentinner tabs">
    <ul>
        <li class="<?=findActive('Kanban'); ?>">
            <a
                <?php if (isset($_SESSION['lastFilterdTicketKanbanView']) && $_SESSION['lastFilterdTicketKanbanView'] != "") { ?>
                    href="<?=$_SESSION['lastFilterdTicketKanbanView'] ?>"
                <?php } else { ?>
                    href="<?=BASE_URL ?>/tickets/showKanban"
                <?php } ?>
            ><?=$tpl->__("links.kanban") ?>
            </a>
        </li>
        <li class="<?=findActive('showAll'); ?>">
            <a
                <?php if (isset($_SESSION['lastFilterdTicketTableView']) && $_SESSION['lastFilterdTicketTableView'] != "") { ?>
                    href="<?=$_SESSION['lastFilterdTicketTableView'] ?>"
                <?php } else { ?>
                    href="<?=BASE_URL ?>/tickets/showAll"
                <?php } ?>
            ><?=$tpl->__("links.table") ?></a>
        </li>
        <li class="<?=findActive('showList'); ?>">
            <a
                <?php if (isset($_SESSION['lastFilterdTicketListView']) && $_SESSION['lastFilterdTicketListView'] != "") { ?>
                    href="<?=$_SESSION['lastFilterdTicketListView'] ?>"
                <?php } else { ?>
                    href="<?=BASE_URL ?>/tickets/showList"
                <?php } ?>
            ><?=$tpl->__("links.list_view") ?>
            </a>
        </li>
        <li class="<?=findActive('roadmap'); ?>">
            <a href="<?=BASE_URL ?>/tickets/roadmap">
                <?=$tpl->__("links.timeline_view") ?>
            </a>
        </li>
        <li class="<?=findActive('Calendar'); ?>">
            <a href="<?=BASE_URL ?>/tickets/showProjectCalendar">
                <?=$tpl->__("links.calendar_view") ?>
            </a>
        </li>
    </ul>
</div>
