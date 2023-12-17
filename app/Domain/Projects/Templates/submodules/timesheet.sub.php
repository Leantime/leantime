<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
if (!$project['hourBudget']) {
    $project['hourBudget'] = 'no';
}
$bookedHours = $tpl->get('bookedHours');
?>

<!--<div style="float:left;">
    <div id="my_chart">&nbsp;</div>
</div>-->

<form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#timesheets" method="post">

<h4>
    <?php echo $bookedHours; ?> hours of <?php echo $project['hourBudget'] ?> estimated hours used.
</h4>
<br/>
<table cellpadding="0" cellspacing="0" width="60%" class="table table-bordered">
    <thead>
        <tr id="toggleBody">
            <th class="head0"><label for="dateFrom"><?php echo $tpl->__('DATE_FROM') ?></label></th>
            <th class="head1"><label for="dateTo"><?php echo $tpl->__('DATE_TO') ?></label></th>
            <th class="head0"><label></label></th>
            <th class="head1"><label></label></th>
            <th class="head0">&nbsp;</th>
        </tr>
    </thead>
    <tr id="body">
        <td><input type="text" id="dateFrom" name="dateFrom"
            value="<?php echo $tpl->get('dateFrom'); ?>" size="7" /></td>
        <td><input type="text" id="dateTo" name="dateTo"
            value="<?php echo $tpl->get('dateTo'); ?>" size="7" /></td>
        <td>
        <label for="userId"><?php echo $tpl->__('EMPLOYEE'); ?></label>
        <select name="userId" id="userId" onchange="submit();">
            <option value="all"><?php echo $tpl->__('ALL_EMPLOYEES'); ?></option>

            <?php foreach ($tpl->get('employees') as $row) {
                echo'<option value="' . $row['id'] . '"';
                if ($row['id'] == $tpl->get('employeeFilter')) {
                    echo' selected="selected" ';
                }
                echo'>' . sprintf($tpl->__("text.full_name"), $tpl->escape($row["firstname"]), $tpl->escape($row['lastname'])) . '</option>';
            }

            ?>
        </select>
        <br />
        <label for="kind"><?php echo $tpl->__('KIND') ?></label>
        <select id="kind" name="kind" onchange="submit();">
            <option value="all"><?php echo $tpl->__('ALL_KINDS') ?></option>
            <?php foreach ($tpl->get('kind') as $row) {
                echo'<option value="' . $row . '"';
                if ($row == $tpl->get('actKind')) {
                    echo ' selected="selected"';
                }
                echo'>' . $tpl->__($row) . '</option>';
            }
            ?>

        </select> </td>
        <td>
        <label for="invEmpl"><?php echo $tpl->__('INVOICED') ?></label>
        <input type="checkbox" value="on" name="invEmpl" id="invEmpl" onclick="submit();"
            <?php
            if ($tpl->get('invEmpl') == '1') {
                echo ' checked="checked"';
            }
            ?>
        /><br />
        <label for="invEmpl"><?php echo $tpl->__('INVOICED_COMP'); ?></label>
        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
            <?php
            if ($tpl->get('invComp') == '1') {
                echo ' checked="checked"';
            }
            ?>
        />
        </td>
        <td><input type="submit" value="<?php echo $tpl->__('FILTER') ?>" class="reload" /></td>
    </tr>

</table>

</form>


<table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
    id="allTickets">
    <colgroup>
                <col class="con0"/>
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
                <col class="con1" />
    </colgroup>
    <thead>
        <tr>
            <th><?php echo $tpl->__('ID'); ?></th>
            <th><?php echo $tpl->__('DATE'); ?></th>
            <th><?php echo $tpl->__('HOURS'); ?></th>
            <th><?php echo $tpl->__('PLANHOURS'); ?></th>
            <th><?php echo $tpl->__('DIFFERENCE_HOURS'); ?></th>
            <th><?php echo $tpl->__('TICKET'); ?></th>
            <th><?php echo $tpl->__('PROJECT'); ?></th>
            <th><?php echo $tpl->__('EMPLOYEE'); ?></th>
            <th><?php echo $tpl->__('KIND'); ?></th>
            <th><?php echo $tpl->__('DESCRIPTION'); ?></th>
            <th><?php echo $tpl->__('INVOICED'); ?></th>
            <th><?php echo $tpl->__('INVOICED_COMP'); ?></th>
        </tr>
    </thead>
    <tbody>

    <?php
    $sum = 0;
    foreach ($tpl->get('allTimesheets') as $row) {
        $sum = $sum + $row['hours'];?>
        <tr>
            <td><a href="<?=BASE_URL ?>/timesheets/editTime/<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
            <td><?php echo format($row['workDate'])->date(); ?></td>
            <td><?php echo $row['hours']; ?></td>
            <td><?php echo $row['planHours']; ?></td>
                <?php $diff = $row['planHours'] - $row['hours']; ?>
            <td <?php if ($diff < 0) {
                echo'class="new" ';
                }?>><?php echo $diff; ?></td>
            <td><a href="#/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php echo $row['headline']; ?></a></td>
            <td><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php echo $row['name']; ?></a></td>
            <td><?php printf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></td>
            <td><?php echo $tpl->__($row['kind']); ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php if ($row['invoicedEmpl'] == '1') {
                ?> <?php echo format($row['invoicedEmplDate'])->date(); ?>
                <?php } else {
                    ?>  <?php
                } ?></td>
            <td><?php if ($row['invoicedComp'] == '1') {
                ?> <?php echo format($row['invoicedCompDate'])->date(); ?>
                <?php } else {
                    ?> <?php
                } ?></td>
        </tr>
    <?php } ?>
        <?php if (count($tpl->get('allTimesheets')) === 0) : ?>
        <tr>
            <td colspan="8"><?php echo $tpl->__('NO_RESULTS'); ?></td>
        </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong><?php echo $tpl->__('ALL_HOURS') ?>:</strong></td>
            <td colspan="8"><strong><?php echo $sum; ?></strong></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>


<script type='text/javascript'>

    jQuery(document).ready(function(){
        jQuery('#toggleBody').click(function(){
            jQuery('#body').toggle();
        });
    });

</script>
