<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$hoursFormat = session('usersettings.hours_format', 'decimal');
?>
<script type="text/javascript">
    // Initialize CSV export formatter (inline to avoid build dependency)
    window.leantime = window.leantime || {};
    window.leantime.timesheetsExport = window.leantime.timesheetsExport || {};

    if (typeof window.leantime.timesheetsExport.resolveCell !== 'function') {
        window.leantime.timesheetsExport.resolveCell = function ($node, fallbackData) {
            if (typeof $node.data('order') === 'undefined') {
                return fallbackData;
            }

            if (! $node.hasClass('js-timesheet-hours')) {
                return $node.data('order');
            }

            var tableFormat = ($node.closest('table[data-hours-format]').data('hoursFormat') || '').toString();

            if (tableFormat === 'human') {
                // jQuery converts data-export-display to exportDisplay in .data()
                if (typeof $node.data('exportDisplay') !== 'undefined') {
                    return $node.data('exportDisplay');
                }

                return $node.text().trim();
            }

            return $node.data('order');
        };
    }

    const projectAllLabel = <?php echo json_encode(strip_tags($tpl->__('menu.all_projects'))); ?>;

    function updateProjectCountInline() {
        var checkedCount = document.querySelectorAll('.project-checkbox:checked').length;
        var allChecked = document.getElementById('projectCheckboxAll') ? document.getElementById('projectCheckboxAll').checked : false;
        var countElement = document.getElementById('projectSelectedCount');

        if (countElement) {
            if (allChecked || checkedCount === 0) {
                countElement.textContent = projectAllLabel;
            } else {
                countElement.textContent = checkedCount + ' project(s) selected';
            }
        }
    }

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

        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);

        // Initialize filter preferences
        setTimeout(function() {

            if (typeof leantimeFilterPreferences !== 'undefined') {

                var dataTable = jQuery('#allTimesheetsTable').DataTable();

                if (dataTable) {
                    leantimeFilterPreferences.init(dataTable);
                } else {
                    console.error('[Profiles] Template: DataTable not found!');
                }

            } else {
                console.error('[Profiles] Template: leantimeFilterPreferences not defined!');
            }
        }, 1000);

        // Close project checkbox dropdown when clicking outside
        jQuery(document).on('click', function(e) {
            if (!jQuery(e.target).closest('.project-dropdown-container').length) {
                var dropdown = document.getElementById('projectCheckboxDropdown');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
            }
        });
    });
</script>

<!-- ADDITIVE: Column State Persistence -->
<script src="<?= BASE_URL ?>/assets/js/app/core/datatablesColumnState.js"></script>
<!-- END ADDITIVE -->

<!-- ADDITIVE: Filter States Persistence -->
<script src="<?= BASE_URL ?>/assets/js/app/timesheets/filterPreferences.js?v=<?= time() ?>"></script>
<!-- END ADDITIVE -->

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-business-time"></span></div>
        <div class="pagetitle">
        <h1><?php echo $tpl->__('headlines.all_timesheets') ?></h1>
    </div>
</div>
<!-- page header -->


<div class="maincontent">
    <div class="maincontentinner">
        <form action="<?php echo BASE_URL ?>/timesheets/showAll" method="post" id="form" name="form">

            <div class="pull-right">
                <div id="tableButtons" style="display:inline-block; vertical-align: middle;"></div>
                <input type="submit" value="<?php echo $tpl->__('buttons.search')?>" class="reload" style="vertical-align: middle;" />
            </div>

            <?php
            // CUSTOM: Add modern search bar component
            $tpl->displaySubmodule('timesheets-timesheetSearchBar');
            ?>

            <div class="clearfix"></div>
            <div class="headtitle" style="">

            <table cellpadding="10" cellspacing="0" width="90%" class="table dataTable filterTable">
                <tr>
                    <td style="vertical-align: top;">
                        <label for="clients"><?php echo $tpl->__('label.client'); ?></label>
                        <select name="clientId">
                            <option value="-1"><?php echo strip_tags($tpl->__('menu.all_clients')) ?></option>
                            <?php foreach ($tpl->get('allClients') as $client) {?>
                                <option value="<?= $client['id'] ?>"
                                    <?php if ($tpl->get('clientFilter') == $client['id']) {
                                        echo "selected='selected'";
                                    } ?>><?= $tpl->escape($client['name'])?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <label><?php echo $tpl->__('label.project'); ?></label>
                        <div class="project-dropdown-container" style="position: relative; width: 200px;">
                            <button type="button" class="project-dropdown-toggle" style="width: 100%; padding: 4px 14px; text-align: left; background: #fff; border: 1px solid #ccc; cursor: pointer; border-radius: 20px; font-size: 14px; line-height: 20px; height: 30px; display: flex; align-items: center; justify-content: space-between; gap: 8px; color: #555; box-sizing: border-box;" onclick="document.getElementById('projectCheckboxDropdown').style.display = document.getElementById('projectCheckboxDropdown').style.display === 'none' ? 'block' : 'none';">
                                <span class="selected-count" id="projectSelectedCount" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php
                                    $selectedProjects = is_array($tpl->get('projectFilter')) ? $tpl->get('projectFilter') : [$tpl->get('projectFilter')];
                                    if ($tpl->get('projectFilter') == -1 || !is_array($tpl->get('projectFilter'))) {
                                        echo strip_tags($tpl->__('menu.all_projects'));
                                    } else {
                                        echo count($selectedProjects) . ' project(s) selected';
                                    }
                                    ?>
                                </span>
                                <i class="fa fa-chevron-down" style="font-size: 10px; flex-shrink: 0;"></i>
                            </button>
                            <div id="projectCheckboxDropdown" class="project-checkbox-dropdown" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #d0d5dd; border-radius: 14px; width: 100%; max-height: 250px; overflow-y: auto; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1); margin-top: 6px;">
                                <label style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #eef2f7; background: #f7f9fc; font-weight: bold; border-top-left-radius: 14px; border-top-right-radius: 14px; cursor: pointer;" onclick="event.stopPropagation();">
                                    <input type="checkbox" name="project[]" value="-1" class="project-checkbox-all" id="projectCheckboxAll" style="margin: 0; vertical-align: middle;"
                                        onchange="if(this.checked) { document.querySelectorAll('.project-checkbox').forEach(function(cb){cb.checked=false;}); } updateProjectCountInline();"
                                        <?php if (!is_array($tpl->get('projectFilter')) || $tpl->get('projectFilter') == -1) {
                                            echo 'checked="checked"';
                                        } ?>>
                                    <span><?php echo strip_tags($tpl->__('menu.all_projects')) ?></span>
                                </label>
                                <?php
                                foreach ($tpl->get('allProjects') as $project) { ?>
                                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; cursor: pointer; color: #333; transition: background-color 0.2s ease;" class="project-checkbox-label" onmouseover="this.style.background='#eef2f7'; this.style.color='#333';" onmouseout="this.style.background='white'; this.style.color='#333';" onclick="event.stopPropagation();">
                                        <input type="checkbox" name="project[]" value="<?= $project['id'] ?>" class="project-checkbox" style="margin: 0; vertical-align: middle;"
                                            onchange="if(this.checked) { document.getElementById('projectCheckboxAll').checked=false; } else { var anyChecked = document.querySelectorAll('.project-checkbox:checked').length > 0; if(!anyChecked) { document.getElementById('projectCheckboxAll').checked=true; } } updateProjectCountInline();"
                                            <?php if (is_array($selectedProjects) && in_array($project['id'], $selectedProjects)) {
                                                echo 'checked="checked"';
                                            } ?>>
                                        <span><?= $tpl->escape($project['name']) ?></span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                    <?php if (! empty($tpl->get('allTickets'))) { ?>
                    <td style="vertical-align: top;">
                        <label for="ticket"><?php echo $tpl->__('label.ticket'); ?></label>
                            <select name="ticket" style="max-width:120px;">
                                <option value="-1"><?php echo strip_tags($tpl->__('menu.all_tickets')) ?></option>
                                <?php foreach ($tpl->get('allTickets') as $ticket) {?>
                                    <option value="<?= $ticket['id'] ?>"
                                        <?php if ($tpl->get('ticketFilter') == $ticket['id']) {
                                            echo "selected='selected'";
                                        } ?>><?= $tpl->escape($ticket['headline'])?></option>
                                <?php } ?>
                            </select>
                    </td>
                    <?php } ?>

                    <td style="vertical-align: top;">
                        <label for="dateFrom"><?php echo $tpl->__('label.date_from'); ?></label>
                        <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom" autocomplete="off"
                        value="<?php echo format($tpl->get('dateFrom'))->date(); ?>" size="5" style="max-width:100px; margin-bottom:10px"/></td>
                    <td style="vertical-align: top;">
                        <label for="dateTo"><?php echo $tpl->__('label.date_to'); ?></label>
                        <input type="text" id="dateTo" class="dateTo" name="dateTo" autocomplete="off"
                        value="<?php echo format($tpl->get('dateTo'))->date(); ?>" size="5" style="max-width:100px; margin-bottom:10px" /></td>
                    <td style="vertical-align: top;">
                    <label for="userId"><?php echo $tpl->__('label.employee'); ?></label>
                        <select name="userId" id="userId" onchange="submit();" style="max-width:120px;">
                            <option value="all"><?php echo $tpl->__('label.all_employees'); ?></option>

                            <?php foreach ($tpl->get('employees') as $row) {
                                echo '<option value="'.$row['id'].'"';
                                if ($row['id'] == $tpl->get('employeeFilter')) {
                                    echo ' selected="selected" ';
                                }
                                echo '>'.sprintf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])).'</option>';
                            }
?>
                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <label for="kind"><?php echo $tpl->__('label.type')?></label>
                        <select id="kind" name="kind" onchange="submit();" style="max-width:120px;">
                            <option value="all"><?php echo $tpl->__('label.all_types'); ?></option>
                            <?php foreach ($tpl->get('kind') as $key => $row) {
                                echo '<option value="'.$key.'"';
                                if ($key == $tpl->get('actKind')) {
                                    echo ' selected="selected"';
                                }
                                echo '>'.$tpl->__($row).'</option>';
                            }
?>

                        </select>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" value="1" name="invEmpl" id="invEmpl" onclick="submit();"
                            <?php
if ($tpl->get('invEmpl') == '1') {
    echo ' checked="checked"';
}
?>
                        />
                        <label for="invEmpl"><?php echo $tpl->__('label.invoiced'); ?></label>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
                            <?php
if ($tpl->get('invComp') == '1') {
    echo ' checked="checked"';
}
?>
                        />
                        <label for="invEmpl"><?php echo $tpl->__('label.invoiced_comp'); ?></label>
                    </td>

                    <td style="vertical-align: top;">
                        <input type="checkbox" value="on" name="paid" id="paid" onclick="submit();"
                            <?php
if ($tpl->get('paid') == '1') {
    echo ' checked="checked"';
}
?>
                        />
                        <label for="paid"><?php echo $tpl->__('label.paid'); ?></label>
                    </td>
                    <td style="vertical-align: top;">
                        <input type="hidden" name='filterSubmit' value="1"/>
                    </td>
                </tr>
            </table>
            </div>
            <div style = "overflow-x:auto;">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable" data-hours-format="<?= $tpl->escape($hoursFormat); ?>">
                <colgroup>
                      <col class="con0" width="100px"/>
                      <col class="con1" width="80px"/>
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
                      <col class="con0"/>
                </colgroup>
                <thead>
                    <tr>
                        <!-- ADDITIVE: data-column-name for state persistence -->
                        <th data-column-name="id"><?php echo $tpl->__('label.id'); ?></th>
                        <th data-column-name="tickId">Tick.ID</th>
                        <th data-column-name="date"><?php echo $tpl->__('label.date'); ?></th>
                        <th data-column-name="ticket"><?php echo $tpl->__('label.ticket'); ?></th>
                        <th data-column-name="hours"><?php echo $tpl->__('label.hours'); ?></th>
                        <th data-column-name="planHours"><?php echo $tpl->__('label.plan_hours'); ?></th>
                        <th data-column-name="difference"><?php echo $tpl->__('label.difference'); ?></th>
                        <th data-column-name="project"><?php echo $tpl->__('label.project'); ?></th>
                        <th data-column-name="client"><?php echo $tpl->__('label.client'); ?></th>
                        <th data-column-name="employee"><?php echo $tpl->__('label.employee'); ?></th>
                        <th data-column-name="type"><?php echo $tpl->__('label.type')?></th>
                        <th data-column-name="milestone"><?php echo $tpl->__('label.milestone') ?></th>
                        <th data-column-name="tags"><?php echo $tpl->__('label.tags') ?></th>
                        <th data-column-name="description"><?php echo $tpl->__('label.description'); ?></th>
                        <th data-column-name="invoiced"><?php echo $tpl->__('label.invoiced'); ?></th>
                        <th data-column-name="mgrApproval"><?php echo $tpl->__('label.invoiced_comp'); ?></th>
                        <th data-column-name="paid"><?php echo $tpl->__('label.paid'); ?></th>
                    </tr>

                </thead>
                <tbody>

                <?php

                $sum = 0;
$billableSum = 0;

foreach ($tpl->get('allTimesheets') as $row) {
    $sum = $sum + $row['hours']; ?>
                    <tr>
                        <td data-order="<?= $tpl->e($row['id']); ?>">
                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <a href="<?= BASE_URL?>/timesheets/editTime/<?= $row['id']?>" class="editTimeModal">#<?= $row['id'].' - '.$tpl->__('label.edit'); ?> </a>
                                <?php } else { ?>
                                #<?= $row['id']?>
                                <?php } ?>
                        </td>
                        <td data-order="<?= $tpl->e($row['ticketId']); ?>">
                                <a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>">#<?php echo $tpl->escape($row['ticketId']); ?></a>
                        </td>
                        <td data-order="<?= $tpl->escape($row['workDate']); ?>">
                                <?php echo format($row['workDate'])->date(); ?>
                        </td>
                        <td data-order="<?= $tpl->e($row['headline']); ?>"><a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $tpl->e($row['headline']); ?></a></td>
                        <td data-order="<?php $tpl->e($row['hours']); ?>" data-export-display="<?php echo format_hours($row['hours']); ?>" class="js-timesheet-hours"><?php echo format_hours($row['hours']); ?></td>
                        <td data-order="<?php $tpl->e($row['planHours']); ?>" data-export-display="<?php echo format_hours($row['planHours']); ?>" class="js-timesheet-hours"><?php echo format_hours($row['planHours']); ?></td>
                            <?php $diff = $row['planHours'] - $row['hours']; ?>
                        <td data-order="<?= $diff; ?>" data-export-display="<?php echo format_hours($diff); ?>" class="js-timesheet-hours"><?php echo format_hours($diff); ?></td>


                        <td data-order="<?= $tpl->e($row['name']); ?>"><a href="<?= BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $tpl->e($row['name']); ?></a></td>
                        <td data-order="<?= $tpl->e($row['clientName']); ?>"><a href="<?= BASE_URL ?>/clients/showClient/<?php echo $row['clientId']; ?>"><?php $tpl->e($row['clientName']); ?></a></td>

                        <td><?php printf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></td>
                        <td><?php echo $tpl->__($tpl->get('kind')[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']); ?></td>

                        <td><?php echo $tpl->escape($row['milestone']); ?></td>
                        <td><?php echo $tpl->escape($row['tags']); ?></td>

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
        <td colspan="4"></td>
        <td><strong style="margin-left:-10px;"><?php echo round($sum,2); ?></strong></td>
        <td colspan="8"></td>
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
            </div>
        </form>
    </div>
</div>
