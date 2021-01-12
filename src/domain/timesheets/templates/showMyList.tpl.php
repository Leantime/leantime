<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$helper = $this->get('helper');
?>


<div class="pageheader">


    <div class="pageicon"><span class="iconfa-time"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('headline.overview'); ?></h5>
        <h1><?php echo $this->__("headline.my_timesheets") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php
        echo $this->displayNotification();
        ?>


        <form action="<?=BASE_URL ?>/timesheets/showMyList" method="post" id="form" name="form">

            <div class="pull-right">
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("links.list_view") ?> <?=$this->__("links.view") ?></button>
                    <ul class="dropdown-menu">
                        <li><a href="<?=BASE_URL?>/timesheets/showMy" ><?=$this->__("links.week_view") ?></a></li>
                        <li><a href="<?=BASE_URL?>/timesheets/showMyList" class="active"><?=$this->__("links.list_view") ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="pull-right" style="margin-right:3px;">
                <div id="tableButtons" style="display:inline-block"></div>
                <a onclick="jQuery('.headtitle').toggle();" class="btn btn-default "><?=$this->__("links.filter") ?></a>
            </div>

            <div class="clearfix"></div>

            <div class="headtitle filterBar " style="margin:0px; background: #eee;">

                <div class="filterBoxLeft">
                    <label for="dateFrom"><?php echo $this->__('label.date_from'); ?> <?php echo $this->__('label.date_to'); ?></label>
                    <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom"
                           value="<?php echo $this->getFormattedDateString($this->get('dateFrom')); ?>" style="margin-bottom:10px; width:90px; float:left; margin-right:10px"/>
                    <input type="text" id="dateTo" class="dateTo" name="dateTo"
                           value="<?php echo $this->getFormattedDateString($this->get('dateTo')); ?>" style="margin-bottom:10px; width:90px" />
                </div>
                <div class="filterBoxLeft">
                    <label for="kind"><?php echo $this->__("label.type")?></label>
                    <select id="kind" name="kind" onchange="submit();">
                        <option value="all"><?php echo $this->__("label.all_types"); ?></option>
                        <?php foreach($this->get('kind') as $key => $row){
                            echo'<option value="'.$key.'"';
                            if($key == $this->get('actKind')) echo ' selected="selected"';
                            echo'>'.$this->__($row).'</option>';

                        }
                        ?>

                    </select>
                </div>
                <div class="filterBoxLeft">
                    <label>&nbsp;</label>
                    <input type="submit" value="<?php echo $this->__('buttons.search')?>" class="reload" />
                </div>
                <div class="clearall"></div>
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
                </colgroup>
                <thead>
                    <tr>
                        <th><?php echo $this->__('label.id'); ?></th>
                        <th><?php echo $this->__('label.date'); ?></th>
                        <th><?php echo $this->__('label.hours'); ?></th>
                        <th><?php echo $this->__('label.plan_hours'); ?></th>
                        <th><?php echo $this->__('label.difference'); ?></th>
                        <th><?php echo $this->__('label.ticket'); ?></th>
                        <th><?php echo $this->__('label.project'); ?></th>
                        <th><?php echo $this->__('label.employee'); ?></th>
                        <th><?php echo $this->__("label.type")?></th>
                        <th><?php echo $this->__('label.description'); ?></th>
                        <th><?php echo $this->__('label.invoiced'); ?></th>
                        <th><?php echo $this->__('label.invoiced_comp'); ?></th>
                    </tr>

                </thead>
                <tbody>

                <?php

                $sum = 0;
                $billableSum = 0;

                foreach($this->get('allTimesheets') as $row) {
                    $sum = $sum + $row['hours'];?>
                    <tr>
                        <td data-order="<?=$this->e($row['id']); ?>"> <a href="<?=BASE_URL?>/timesheets/editTime/<?=$row['id']?>" class="editTimeModal">#<?=$row['id']." - ".$this->__('label.edit'); ?> </a></td>
                        <td data-order="<?php echo $this->getFormattedDateString($row['workDate']); ?>">
                            <?php echo $this->getFormattedDateString($row['workDate']); ?>
                        </td>
                        <td data-order="<?php $this->e($row['hours']); ?>"><?php $this->e($row['hours']); ?></td>
                        <td data-order="<?php $this->e($row['planHours']); ?>"><?php $this->e($row['planHours']); ?></td>
                        <?php $diff = $row['planHours']-$row['hours']; ?>
                        <td data-order="<?php $diff; ?>"><?php echo $diff; ?></td>
                        <td data-order="<?=$this->e($row['headline']); ?>"><a href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $row['ticketId']; ?>"><?php $this->e($row['headline']); ?></a></td>

                        <td data-order="<?=$this->e($row['name']); ?>"><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $row['projectId']; ?>"><?php $this->e($row['name']); ?></a></td>
                        <td><?php sprintf( $this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></td>
                        <td><?php echo $this->__($this->get('kind')[$row['kind']]); ?></td>
                        <td><?php $this->e($row['description']); ?></td>
                        <td data-order="<?php if($row['invoicedEmpl'] == '1'){ echo $this->getFormattedDateString($row['invoicedEmplDate']); }?>"><?php if($row['invoicedEmpl'] == '1'){?> <?php echo $this->getFormattedDateString($row['invoicedEmplDate']); ?>
                        <?php }else{
                                echo $this->__("label.pending");
                            } ?></td>
                        <td data-order="<?php if($row['invoicedComp'] == '1'){ echo $this->getFormattedDateString($row['invoicedCompDate']); }?>">
                            <?php if($row['invoicedComp'] == '1'){?> <?php echo $this->getFormattedDateString($row['invoicedCompDate']); ?>
                        <?php }else{
                                echo $this->__("label.pending");
                            } ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="1"><strong><?php echo $this->__("label.total_hours")?></strong></td>
                        <td colspan="10"><strong><?php echo $sum; ?></strong></td>


                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){
        leantime.timesheetsController.initTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        jQuery(".dateFrom, .dateTo").datepicker({
            numberOfMonths: 1,
            dateFormat:  leantime.i18n.__("language.jsdateformat"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            currentText: leantime.i18n.__("language.currentText"),
            closeText: leantime.i18n.__("language.closeText"),
            buttonText: leantime.i18n.__("language.buttonText"),
            nextText: leantime.i18n.__("language.nextText"),
            prevText: leantime.i18n.__("language.prevText"),
            weekHeader: leantime.i18n.__("language.weekHeader"),
            isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
        });
    });

</script>
