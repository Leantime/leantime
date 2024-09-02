<?php

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#checkAllEmpl").change(function(){
            jQuery(".invoicedEmpl").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".invoicedEmpl").attr("checked", "checked");
                jQuery(".invoicedEmpl").parent().addClass("checked");
            } else {
                jQuery(".invoicedEmpl").removeAttr("checked");
                jQuery(".invoicedEmpl").parent().removeClass("checked");
            }
        });

        jQuery("#checkAllComp").change(function(){
            jQuery(".invoicedComp").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".invoicedComp").attr("checked", "checked");
                jQuery(".invoicedComp").parent().addClass("checked");
            } else {
                jQuery(".invoicedComp").removeAttr("checked");
                jQuery(".invoicedComp").parent().removeClass("checked");
            }
        });

        jQuery("#checkAllPaid").change(function(){
            jQuery(".paid").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".paid").attr("checked", "checked");
                jQuery(".paid").parent().addClass("checked");
            } else {
                jQuery(".paid").removeAttr("checked");
                jQuery(".paid").parent().removeClass("checked");
            }
        });

        leantime.timesheetsController.initTimesheetsTable();

        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
            leantime.timesheetsController.initEditTimeModal();
        <?php } ?>

        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1)
    });
</script>

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-business-time"></span></div>
        <div class="pagetitle">
        <h1><?php echo $tpl->__("headlines.all_timesheets") ?></h1>
    </div>
</div>
<!-- page header -->


<div class="maincontent">
    <div class="maincontentinner">
        <form action="<?php echo BASE_URL ?>/timesheets/showAll" method="post" id="form" name="form">

            <div class="pull-right">
                <div id="tableButtons" style="display:inline-block"></div>
            </div>
            <div class="clearfix"></div>
            <div class="headtitle" style="">

            <table cellpadding="10" cellspacing="0" width="90%" class="table dataTable filterTable">
                <tr>
                    <td>
                        <label for="clients"><?php echo $tpl->__('label.client'); ?></label>
                        <select name="clientId">
                            <option value="-1"><?php echo strip_tags($tpl->__("menu.all_clients")) ?></option>
                            <?php foreach ($tpl->get('allClients') as $client) {?>
                                <option value="<?=$client['id'] ?>"
                                    <?php if ($tpl->get('clientFilter') == $client['id']) {
                                        echo "selected='selected'";
                                    } ?>><?=$tpl->escape($client['name'])?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <label for="projects"><?php echo $tpl->__('label.project'); ?></label>
                        <select name="project" style="max-width:120px;">
                            <option value="-1"><?php echo strip_tags($tpl->__("menu.all_projects")) ?></option>
                            <?php foreach ($tpl->get('allProjects') as $project) {?>
                                <option value="<?=$project['id'] ?>"
                                    <?php if ($tpl->get('projectFilter') == $project['id']) {
                                        echo "selected='selected'";
                                    } ?>><?=$tpl->escape($project['name'])?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <label for="dateFrom"><?php echo $tpl->__('label.date_from'); ?></label>
                        <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom" autocomplete="off"
                        value="<?php echo format($tpl->get('dateFrom'))->date(); ?>" size="5" style="max-width:100px; margin-bottom:10px"/></td>
                    <td>
                        <label for="dateTo"><?php echo $tpl->__('label.date_to'); ?></label>
                        <input type="text" id="dateTo" class="dateTo" name="dateTo" autocomplete="off"
                        value="<?php echo format($tpl->get('dateTo'))->date(); ?>" size="5" style="max-width:100px; margin-bottom:10px" /></td>
                    <td>
                    <label for="userId"><?php echo $tpl->__("label.employee"); ?></label>
                        <select name="userId" id="userId" onchange="submit();" style="max-width:120px;">
                            <option value="all"><?php echo $tpl->__("label.all_employees"); ?></option>

                            <?php foreach ($tpl->get('employees') as $row) {
                                echo'<option value="' . $row['id'] . '"';
                                if ($row['id'] == $tpl->get('employeeFilter')) {
                                    echo' selected="selected" ';
                                }
                                echo'>' . sprintf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <label for="kind"><?php echo $tpl->__("label.type")?></label>
                        <select id="kind" name="kind" onchange="submit();" style="max-width:120px;">
                            <option value="all"><?php echo $tpl->__("label.all_types"); ?></option>
                            <?php foreach ($tpl->get('kind') as $key => $row) {
                                echo'<option value="' . $key . '"';
                                if ($key == $tpl->get('actKind')) {
                                    echo ' selected="selected"';
                                }
                                echo'>' . $tpl->__($row) . '</option>';
                            }
                            ?>

                        </select>
                    </td>
                    <td>
                        <input type="checkbox" value="on" name="invEmpl" id="invEmpl" onclick="submit();"
                            <?php
                            if ($tpl->get('invEmpl') == '1') {
                                echo ' checked="checked"';
                            }
                            ?>
                        />
                        <label for="invEmpl"><?php echo $tpl->__("label.invoiced"); ?></label>
                    </td>
                    <td>
                        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
                            <?php
                            if ($tpl->get('invComp') == '1') {
                                echo ' checked="checked"';
                            }
                            ?>
                        />
                        <label for="invEmpl"><?php echo $tpl->__("label.invoiced_comp"); ?></label>
                    </td>

                    <td>
                        <input type="checkbox" value="on" name="paid" id="paid" onclick="submit();"
                            <?php
                            if ($tpl->get('paid') == '1') {
                                echo ' checked="checked"';
                            }
                            ?>
                        />
                        <label for="paid"><?php echo $tpl->__("label.paid"); ?></label>
                    </td>
                    <td>
                        <input type="hidden" name='filterSubmit' value="1"/>
                        <input type="submit" value="<?php echo $tpl->__('buttons.search')?>" class="reload" />
                    </td>
                </tr>
            </table>
            </div>

            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable">
                <colgroup>
                      <col class="con0" width="100px"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1"/>
                      <col class="con0"/>
                      <col class="con1"/>
                      <col class="con0"/>
                      <col class="con1"/>
                </colgroup>
                <thead>
                    <tr>
                        <th><?php echo $tpl->__('label.id'); ?></th>
                        <th><?php echo $tpl->__('label.date'); ?></th>
                        <th><?php echo $tpl->__('label.hours'); ?></th>
                        <th><?php echo $tpl->__('label.plan_hours'); ?></th>
                        <th><?php echo $tpl->__('label.difference'); ?></th>
                        <th><?php echo $tpl->__('label.ticket'); ?></th>
                        <th><?php echo $tpl->__('label.project'); ?></th>
                        <th><?php echo $tpl->__('label.client'); ?></th>
                        <th><?php echo $tpl->__('label.employee'); ?></th>
                        <th><?php echo $tpl->__("label.type")?></th>
                        <th><?php echo $tpl->__("label.milestone") ?></th>
                        <th><?php echo $tpl->__("label.tags") ?></th>
                        <th><?php echo $tpl->__('label.description'); ?></th>
                        <th><?php echo $tpl->__('label.invoiced'); ?></th>
                        <th><?php echo $tpl->__('label.invoiced_comp'); ?></th>
                        <th><?php echo $tpl->__('label.paid'); ?></th>
                    </tr>

                </thead>
                <tbody>

                <?php

                $sum = 0;
                $billableSum = 0;

                foreach ($tpl->get('allTimesheets') as $row) {
                    $sum = $sum + $row['hours'];?>
                    <tr>
                        <td data-order="<?=$tpl->e($row['id']); ?>">
                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <a href="<?=BASE_URL?>/timesheets/editTime/<?=$row['id']?>" class="editTimeModal">#<?=$row['id'] . " - " . $tpl->__('label.edit'); ?> </a>
                                <?php } else { ?>
                                #<?=$row['id']?>
                                <?php } ?>
                        </td>
                        <td data-order="<?=$tpl->escape($row['workDate']); ?>">
                                <?php echo format($row['workDate'])->date(); ?>
                        </td>
                        <td data-order="<?php $tpl->e($row['hours']); ?>"><?php $tpl->e($row['hours']); ?></td>
                        <td data-order="<?php $tpl->e($row['planHours']); ?>"><?php $tpl->e($row['planHours']); ?></td>
                            <?php $diff = $row['planHours'] - $row['hours']; ?>
                        <td data-order="<?=$diff; ?>"><?php echo $diff; ?></td>
                        <td data-order="<?=$tpl->e($row['headline']); ?>"><a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $tpl->e($row['headline']); ?></a></td>

                        <td data-order="<?=$tpl->e($row['name']); ?>"><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $tpl->e($row['name']); ?></a></td>
                        <td data-order="<?=$tpl->e($row['clientName']); ?>"><a href="<?=BASE_URL ?>/clients/showClient/<?php echo $row['clientId']; ?>"><?php $tpl->e($row['clientName']); ?></a></td>

                        <td><?php printf($tpl->__("text.full_name"), $tpl->escape($row["firstname"]), $tpl->escape($row['lastname'])); ?></td>
                        <td><?php echo $tpl->__($tpl->get('kind')[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']); ?></td>

                        <td><?php echo $tpl->escape($row["milestone"]); ?></td>
                        <td><?php echo $tpl->escape($row["tags"]); ?></td>

                        <td><?php $tpl->e($row['description']); ?></td>
                        <td data-order="<?php if ($row['invoicedEmpl'] == '1') {
                            echo format($row['invoicedEmplDate'])->date();
                                        }?>"><?php if ($row['invoicedEmpl'] == '1') {
    ?> <?php echo format($row['invoicedEmplDate'])->date(); ?>
                                        <?php } else { ?>
                                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <input type="checkbox" name="invoicedEmpl[]" class="invoicedEmpl"
                            value="<?php echo $row['id']; ?>" /> <?php
                                            } ?><?php
                                        } ?></td>
                        <td data-order="<?php if ($row['invoicedComp'] == '1') {
                            echo format($row['invoicedCompDate'])->date();
                                        }?>">

                            <?php if ($row['invoicedComp'] == '1') {?>
                                <?php echo format($row['invoicedCompDate'])->date(); ?>
                            <?php } else { ?>
                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <input type="checkbox" name="invoicedComp[]" class="invoicedComp" value="<?php echo $row['id']; ?>" />
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td data-order="<?php if ($row['paid'] == '1') {
                            echo format($row['paidDate'])->date();
                                        }?>">

                            <?php if ($row['paid'] == '1') {?>
                                <?php echo format($row['paidDate'])->date(); ?>
                            <?php } else { ?>
                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                    <input type="checkbox" name="paid[]" class="paid" value="<?php echo $row['id']; ?>" />
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong><?php echo $tpl->__("label.total_hours")?></strong></td>
                        <td colspan="10"><strong><?php echo $sum; ?></strong></td>

                        <td>
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                            <input type="submit" class="button" value="<?php echo $tpl->__('buttons.save'); ?>" name="saveInvoice" />
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                            <input type="checkbox" id="checkAllEmpl" style="vertical-align: baseline;"/> <?php echo $tpl->__('label.select_all')?></td>
                            <?php } ?>
                        <td>
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                            <input type="checkbox"  id="checkAllComp" style="vertical-align: baseline;"/> <?php echo $tpl->__('label.select_all')?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <input type="checkbox"  id="checkAllPaid" style="vertical-align: baseline;"/> <?php echo $tpl->__('label.select_all')?>
                            <?php } ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>
