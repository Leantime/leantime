<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$sprints = $tpl->get('sprints');
$searchCriteria = $tpl->get('searchCriteria');
$currentSprint = $tpl->get('currentSprint');

$allTicketGroups = $tpl->get('allTickets');

echo $tpl->displayNotification();

$efforts = $tpl->get('efforts');
$priorities = $tpl->get('priorities');
$statusLabels = $tpl->get('allTicketStates');
$groupBy = $tpl->get('groupBy');
$newField = $tpl->get('newField');

// All states >0 (<1 is archive)
$numberofColumns = count($tpl->get('allTicketStates')) - 1;
$size = floor(100 / $numberofColumns);

?>

<?php $tpl->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $tpl->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');

$tpl->displaySubmodule('tickets-ticketNewBtn');
$tpl->displaySubmodule('tickets-ticketFilter');

$tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">

            </div>
        </div>

        <div class="clearfix"></div>

        <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        <div class="row">
            <div class="col-md-3">
                <div class="quickAddForm" style="margin-top:15px;">
                    <form action="" method="post">
                        <input type="text" name="headline" autofocus placeholder="<?php echo $tpl->__('input.placeholders.create_task'); ?>" style="width: 100%;"/>
                        <input type="hidden" name="sprint" value="<?= $currentSprint?>" />
                        <input type="hidden" name="quickadd" value="1"/>
                        <input type="submit" class="btn btn-primary tw-mb-m" value="<?php echo $tpl->__('buttons.save'); ?>" name="saveTicket" style="vertical-align: top; "/>
                    </form>


                    <?php foreach ($allTicketGroups as $group) {?>
                        <?php if ($group['label'] != 'all') { ?>
                            <h5 class="accordionTitle <?= $group['class']?>" id="accordion_link_<?= $group['id'] ?>">
                                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?= $group['id'] ?>" onclick="leantime.snippets.accordionToggle('<?= $group['id'] ?>');">
                                    <i class="fa fa-angle-down"></i><?= $group['label'] ?> (<?= count($group['items']) ?>)
                                </a>
                            </h5>
                            <div class="simpleAccordionContainer" id="accordion_content-<?= $group['id'] ?>">
                        <?php } ?>

                        <?php $allTickets = $group['items']; ?>


                        <table class="table display listStyleTable" style="width:100%">

                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
                            <thead>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                            <tr style="display:none;">

                                <th style="width:20px" class="status-col"><?= $tpl->__('label.todo_status'); ?></th>
                                <th><?= $tpl->__('label.title'); ?></th>
                            </tr>

                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
                            </thead>

                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
                            <tbody>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                            <?php foreach ($allTickets as $rowNum => $row) {?>
                                <tr onclick="leantime.ticketsController.loadTicketToContainer('<?= $row['id']?>', '#ticketContent')" id="row-<?= $row['id']?>" class="ticketRows">
                                    <?php $tpl->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                                    <td data-order="<?= isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['sortKey'] : '' ?>" data-search="<?= isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : '' ?>" class="roundStatusBtn" style="width:20px">
                                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                                            <a class="dropdown-toggle status <?= isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['class'] : '' ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?= $row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?= $row['id']?>">
                                                <li class="nav-header border"><?= $tpl->__('dropdown.choose_status')?></li>
                                                <?php foreach ($statusLabels as $key => $label) {
                                                    echo "<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='".$label['class']."' data-label='".$tpl->escape($label['name'])."' data-value='".$row['id'].'_'.$key.'_'.$label['class']."' id='ticketStatusChange".$row['id'].$key."' >".$tpl->escape($label['name']).'</a>';
                                                    echo '</li>';
                                                }?>
                                            </ul>
                                        </div>
                                    </td>

                                    <td data-search="<?= isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : ''; ?>" data-order="<?= $tpl->e($row['headline']); ?>" >
                                        <a href="javascript:void(0);"><strong><?= $tpl->e($row['headline']); ?></strong></a></td>

                                    <?php $tpl->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                                </tr>
                            <?php } ?>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                            </tbody>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
                        </table>

                        <?php if ($group['label'] != 'all') { ?>
                            </div>
                        <?php } ?>
                    <?php } ?>

                </div>
            </div>
            <div class="col-md-9 hidden-sm"  >
                <div id="ticketContent">
                    <div class="center">
                        <div class='svgContainer'>
                            <?= file_get_contents(ROOT.'/dist/images/svg/undraw_design_data_khdb.svg'); ?>
                        </div>

                        <h3><?= $tpl->__('headlines.pick_a_task')?></h3>
                        <?= $tpl->__('text.edit_tasks_in_here'); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>


        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        leantime.ticketsController.initStatusDropdown();
        <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
        <?php } ?>



        leantime.ticketsController.initTicketsList("<?= $searchCriteria['groupBy'] ?>");

        <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
