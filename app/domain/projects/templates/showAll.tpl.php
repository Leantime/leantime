<?php

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
$menuTypes = $tpl->get('menuTypes');
$showClosedProjects = $tpl->get('showClosedProjects');

?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration');  $tpl->__("") ?></h5>
        <h1><?php echo $tpl->__('headline.all_projects') ?></h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <div class="pull-right">
            <form action="" method="post">
                <input type="hidden" name="hideClosedProjects" value="1" />
                <input type="checkbox" name="showClosedProjects" onclick="form.submit();" id="showClosed" <?php if ($showClosedProjects) {
                    echo"checked='checked'";
                                                                                                          } ?> />&nbsp;<label for="showClosed" class="pull-right">Show Closed Projects</label>
            </form>
        </div>

        <?php echo $tpl->displayLink('projects.newProject', "<i class='fa fa-plus'></i> " . $tpl->__('link.new_project'), null, array('class' => 'btn btn-primary btn-rounded')) ?>
        <div class="clearall"></div>
        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allProjectsTable">
            <?php if ($config->enableMenuType) { ?>
            <colgroup>
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0"/>
                <col class="con1"/>
            </colgroup>
            <thead>
                <tr>
                    <th class="head0"><?php echo $tpl->__('label.project_name'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.client_product'); ?></th>
                    <th class="head0"><?php echo $tpl->__('label.menu_type'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.project_state'); ?></th>
                    <th class="head0"><?php echo $tpl->__('label.num_tickets'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.hourly_budget'); ?></th>
                    <th class="head0"><?php echo $tpl->__('label.budget_cost'); ?></th>
                </tr>
            </thead>
            <?php } else { ?>
            <colgroup>
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0" />
                <col class="con1"/>
                <col class="con0"/>
            </colgroup>
            <thead>
                <tr>
                    <th class="head0"><?php echo $tpl->__('label.project_name'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.client_product'); ?></th>
                    <th class="head0"><?php echo $tpl->__('label.project_state'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.num_tickets'); ?></th>
                    <th class="head0"><?php echo $tpl->__('label.hourly_budget'); ?></th>
                    <th class="head1"><?php echo $tpl->__('label.budget_cost'); ?></th>
                </tr>
            </thead>
            <?php } ?>
            <tbody>

             <?php foreach ($tpl->get('allProjects') as $row) : ?>
                <tr class='gradeA'>

                    <td style="padding:6px;">
                        <?php echo $tpl->displayLink('projects.showProject', $tpl->escape($row['name']), array('id' => $row['id'])) ?>
                    <td>
                        <?php echo $tpl->displayLink('clients.showClient', $tpl->escape($row['clientName']), array('id' => $row['clientId']), null, true) ?>
                    </td>
                    <?php if ($config->enableMenuType) {
                        ?><td><?php echo $menuTypes[$row['menuType']] ?? \Leantime\Domain\Menu\Repositories\Menu::DEFAULT_MENU ?><?php
                    } ?>
                    <td><?php if ($row['state'] == -1) {
                        echo $tpl->__('label.closed');
                        } else {
                            echo $tpl->__('label.open');
                        } ?></td>
                    <td class="center"><?php echo $row['numberOfTickets']; ?></td>
                    <td class="center"><?php $tpl->e($row['hourBudget']); ?></td>
                    <td class="center"><?php $tpl->e($row['dollarBudget']); ?></td>
                </tr>
             <?php endforeach; ?>

            </tbody>
        </table>

    </div>
</div>



<script type="text/javascript">
    jQuery(document).ready(function() {



            leantime.projectsController.initProjectTable();

        }
    );

</script>
