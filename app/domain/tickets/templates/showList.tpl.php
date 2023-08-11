<?php

    defined('RESTRICTED') or die('Restricted access');

    echo $this->displayNotification();

    $sprints        = $this->get("sprints");
    $searchCriteria = $this->get("searchCriteria");
    $currentSprint  = $this->get("currentSprint");
    $allTickets     = $this->get('allTickets');

    $todoTypeIcons  = $this->get("ticketTypeIcons");

    $efforts        = $this->get('efforts');
    $priorities     = $this->get('priorities');
    $statusLabels   = $this->get('allTicketStates');
    $groupBy        = $this->get('groupBy');
    $newField       = $this->get('newField');

    //All states >0 (<1 is archive)
    $numberofColumns = count($this->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

?>

<?php $this->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $this->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $this->dispatchTplEvent('filters.afterLefthandSectionOpen');

                $this->displaySubmodule('tickets-ticketNewBtn');
                $this->displaySubmodule('tickets-ticketFilter');

                $this->dispatchTplEvent('filters.beforeLefthandSectionClose');
                ?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">

            </div>
        </div>

        <div class="clearfix"></div>

        <?php $this->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        <div class="row">
            <div class="col-md-4">
                <div class="quickAddForm" style="margin-top:15px;">
                    <form action="" method="post">
                        <input type="text" name="headline" autofocus placeholder="<?php echo $this->__("input.placeholders.create_task"); ?>" style="margin-bottom: 15px; margin-top: 3px; width: 320px;"/>
                        <input type="hidden" name="sprint" value="<?=$currentSprint?>" />
                        <input type="hidden" name="quickadd" value="1"/>
                        <input type="submit" class="btn btn-default" value="<?php echo $this->__('buttons.save'); ?>" name="saveTicket" style="vertical-align: top; margin-top:3px;  width:100px;"/>
                    </form>
                    <table id="allTicketsTable" class="table display listStyleTable" style="width:100%">

                        <?php $this->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
                        <thead>
                        <?php $this->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                        <tr style="display:none;">

                            <th style="width:20px" class="status-col"><?= $this->__("label.todo_status"); ?></th>
                            <th><?= $this->__("label.title"); ?></th>

                            <th class="milestone-col"><?= $this->__("label.milestone"); ?></th>
                            <th class="priority-col"><?= $this->__("label.priority"); ?></th>
                            <th class="user-col"><?= $this->__("label.editor"); ?>.</th>
                            <th class="sprint-col"><?= $this->__("label.sprint"); ?></th>
                            <th class="tags-col"><?= $this->__("label.tags"); ?></th>
                        </tr>

                        <?php $this->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
                        </thead>

                        <?php $this->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
                        <tbody>
                        <?php $this->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                        <?php foreach ($allTickets as $rowNum => $row) {?>
                            <tr onclick="leantime.ticketsController.loadTicketToContainer('<?=$row['id']?>', '#ticketContent')" id="row-<?=$row['id']?>" class="ticketRows">
                                <?php $this->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                                <td data-order="<?=$statusLabels[$row['status']]["sortKey"]; ?>" data-search="<?=$statusLabels[$row['status']]["name"]; ?>" class="roundStatusBtn" style="width:20px">
                                    <div class="dropdown ticketDropdown statusDropdown colorized show">
                                        <a class="dropdown-toggle status <?=isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]["class"] : '' ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$row['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$row['id']?>">
                                            <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>
                                            <?php foreach ($statusLabels as $key => $label) {
                                                echo"<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $this->escape($label["name"]) . "' data-value='" . $row['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $row['id'] . $key . "' >" . $this->escape($label["name"]) . "</a>";
                                                echo"</li>";
                                            }?>
                                        </ul>
                                    </div>
                                </td>

                                <td data-search="<?=$statusLabels[$row['status']]["name"]; ?>" data-order="<?=$this->e($row['headline']); ?>" >
                                    <a href="javascript:void(0);"><strong><?=$this->e($row['headline']); ?></strong></a></td>
                                <td data-search="<?=$this->escape($row['milestoneHeadline']) ?>" data-order="<?=$this->escape($row['milestoneHeadline']) ?>"><?=$this->escape($row['milestoneHeadline']) ?></td>
                                <td data-search="<?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?>" data-order="<?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?>"><?=$row['priority'] ? $priorities[$row['priority']] : $this->__("label.priority_unkown"); ?></td>
                                <td data-search="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>" data-order="<?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?>"><?=$row["editorFirstname"] != "" ?  $this->escape($row["editorFirstname"]) : $this->__("dropdown.not_assigned")?></td>
                                <td data-search="<?=$this->escape($row['sprintName']); ?>"><?=$this->escape($row['sprintName']); ?></td>
                                <td data-search="<?=$row['tags'] ?>">
                                    <?php if ($row['tags'] != '') {?>
                                        <?php  $tagsArray = explode(",", $row['tags']); ?>
                                        <div class='tagsinput readonly'>
                                            <?php

                                            foreach ($tagsArray as $tag) {
                                                echo"<span class='tag'><span>" . $tag . "</span></span>";
                                            }

                                            ?>
                                        </div>
                                    <?php } ?>
                                </td>


                                <?php $this->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                            </tr>
                        <?php } ?>
                        <?php $this->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                        </tbody>
                        <?php $this->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
                    </table>
                </div>
            </div>
            <div class="col-md-8 hidden-sm"  >
                <div id="ticketContent">
                    <div class="center">
                        <div class='svgContainer'>
                            <?=file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg"); ?>
                        </div>

                        <h3><?=$this->__("headlines.pick_a_task")?></h3>
                        <?=$this->__("text.edit_tasks_in_here"); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>


        leantime.timesheetsController.initTicketTimers();


        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initStatusDropdown();
        <?php } else { ?>
        leantime.authController.makeInputReadonly(".maincontentinner");
        <?php } ?>



        leantime.ticketsController.initTicketsList("<?=$searchCriteria["groupBy"] ?>");

        <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

    });

</script>
