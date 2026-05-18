
<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');

?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headline.new_project') ?></h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><?php echo $tpl->__('tabs.projectdetails'); ?></a></li>
            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="">

                    <div class="row">

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $tpl->e($project['name']) ?>" placeholder="<?= $tpl->__('input.placeholders.enter_title_of_project')?>"/>
                                    </div>
                                    <input type="hidden" name="projectState"  id="projectState" value="0" />

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <br />
                                    <p>
                                        <?php echo $tpl->__('label.accomplish'); ?>
                                        <?php echo $tpl->__('label.describe_outcome'); ?>
                                        <br /><br />
                                    </p>
                                    <textarea name="details" id="details" class="tiptapComplex" rows="5" cols="50"><?php echo htmlentities($project['details']) ?></textarea>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <br />
                                    <h4 class="widgettitle title-light"><span class="fa fa-map"></span><?php echo $tpl->__('headline.milestones'); ?></h4>
                                    <p><?php echo $tpl->__('text.milestones_help_organize_projects'); ?></p>

                                    <div id="projectMilestones">
                                        <?php
                                        $milestones = $project['milestones'] ?? [['headline' => '', 'editFrom' => '', 'editTo' => '']];
foreach ($milestones as $index => $milestone) {
    ?>
                                            <div class="project-milestone-row row" style="margin-bottom: 12px;">
                                                <div class="col-md-4">
                                                    <input type="text"
                                                           name="milestones[<?php echo $index; ?>][headline]"
                                                           value="<?php $tpl->e($milestone['headline'] ?? '') ?>"
                                                           placeholder="<?php echo $tpl->__('label.milestone_title'); ?>"
                                                           style="width: 100%;" />
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text"
                                                           class="dates milestoneDateFrom"
                                                           name="milestones[<?php echo $index; ?>][editFrom]"
                                                           value="<?php $tpl->e($milestone['editFrom'] ?? '') ?>"
                                                           placeholder="<?php echo $tpl->__('label.planned_start_date'); ?>"
                                                           autocomplete="off"
                                                           style="width: 100%;" />
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text"
                                                           class="dates milestoneDateTo"
                                                           name="milestones[<?php echo $index; ?>][editTo]"
                                                           value="<?php $tpl->e($milestone['editTo'] ?? '') ?>"
                                                           placeholder="<?php echo $tpl->__('label.planned_end_date'); ?>"
                                                           autocomplete="off"
                                                           style="width: 100%;" />
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number"
                                                           name="milestones[<?php echo $index; ?>][weight]"
                                                           value="<?php $tpl->e($milestone['weight'] ?? '') ?>"
                                                           placeholder="<?php echo $tpl->__('label.milestone_weight'); ?>"
                                                           min="1" max="100"
                                                           style="width: 100%;" />
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-default removeMilestone" data-tippy-content="<?php echo $tpl->__('buttons.delete'); ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <button type="button" id="addMilestone" class="btn btn-default">
                                        <?php echo $tpl->__('links.add_milestone'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="padding-top">
                                <?php if (isset($project['id']) && $project['id'] != '') { ?>
                                    <div class="pull-right padding-top">
                                        <a href="<?= BASE_URL?>/projects/delProject/<?php echo $project['id']?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('buttons.delete'); ?></a>
                                    </div>
                                <?php } ?>
                                <input type="submit" name="save" id="save" class="button" value="<?php echo $tpl->__('buttons.save'); ?>" class="button" />

                            </div>
                        </div>

                        <div class="col-md-4">

                            <?php if ($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1) {?>
                                <h4 class="widgettitle title-light"><i class="fa-regular fa-rectangle-list"></i> Project Type</h4>
                                <p>The type of the project. This will determine which features are available.</p>
                                <select name="type">
                                    <?php foreach ($tpl->get('projectTypes') as $key => $type) { ?>
                                        <option value="<?= $tpl->escape($key)?>"
                                            <?php if ($project['type'] == $key) {
                                                echo " selected='selected' ";
                                            } ?>
                                        ><?= $tpl->__($tpl->escape($type))?></option>
                                    <?php } ?>
                                </select>
                                <br /><br />
                            <?php } ?>

                            <?php $tpl->dispatchTplEvent('beforeClientPicker', $project) ?>

                            <div style="margin-bottom: 30px;">
                                <h4 class="widgettitle title-light tw-block"><span
                                        class="fa fa-calendar"></span><?php echo $tpl->__('label.project_dates'); ?></h4>
                                <div>
                                    <label><?php echo $tpl->__('label.project_start'); ?></label>
                                    <div class="">
                                        <input type="text" class="dates dateFrom" style="width:100px;" name="start" autocomplete="off"
                                               value="<?php echo $project['start']; ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>

                                    </div>
                                    <label ><?php echo $tpl->__('label.project_end'); ?></label>
                                    <div class="">
                                        <input type="text" class="dates dateTo" style="width:100px;" name="end" autocomplete="off"
                                               value="<?php echo $project['end']; ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>

                                    </div>
                                </div>

                            </div>

                            <div style="margin-bottom: 30px;">

                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span><?php echo $tpl->__('label.client_product'); ?></h4>
                                    <select name="clientId" id="clientId">

                                        <?php foreach ($tpl->get('clients') as $row) { ?>
                                            <option value="<?php echo $row['id']; ?>"
                                                <?php if ($project['clientId'] == $row['id']) {
                                                    ?> selected=selected
                                                <?php } ?>><?php $tpl->e($row['name']); ?></option>
                                        <?php } ?>

                                    </select>
                                    <?php if ($login::userIsAtLeast('manager')) { ?>
                                        <br /><a href="<?= BASE_URL?>/clients/newClient" target="_blank"><?= $tpl->__('label.client_not_listed'); ?></a>
                                    <?php } ?>


                                </div>
                            </div>

                            <div style="margin-bottom: 30px;">
                                <div class="">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span><?php echo $tpl->__('labels.defaultaccess'); ?></h4>
                                    <?php echo $tpl->__('text.who_can_access'); ?>
                                    <br /><br />

                                    <select name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" <?= $project['psettings'] == 'restricted' ? "selected='selected'" : '' ?>><?php echo $tpl->__('labels.only_chose'); ?></option>
                                        <option value="clients" <?= $project['psettings'] == 'clients' ? "selected='selected'" : ''?>><?php echo $tpl->__('labels.everyone_in_client'); ?></option>
                                        <option value="all" <?= $project['psettings'] == 'all' ? "selected='selected'" : ''?>><?php echo $tpl->__('labels.everyone_in_org'); ?></option>
                                    </select>

                                </div>
                            </div>

                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        jQuery("#projectdetails select").chosen();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 2);
        leantime.dateController.initDateRangePicker(".milestoneDateFrom", ".milestoneDateTo", 2);

        var milestoneIndex = jQuery("#projectMilestones .project-milestone-row").length;
        var updateRemoveButtons = function() {
            jQuery("#projectMilestones .removeMilestone").prop("disabled", jQuery("#projectMilestones .project-milestone-row").length <= 1);
        };

        jQuery("#addMilestone").on("click", function() {
            var row = '' +
                '<div class="project-milestone-row row" style="margin-bottom: 12px;">' +
                    '<div class="col-md-4">' +
                        '<input type="text" name="milestones[' + milestoneIndex + '][headline]" placeholder="<?php echo $tpl->__('label.milestone_title'); ?>" style="width: 100%;" />' +
                    '</div>' +
                    '<div class="col-md-2">' +
                        '<input type="text" class="dates milestoneDateFrom" name="milestones[' + milestoneIndex + '][editFrom]" placeholder="<?php echo $tpl->__('label.planned_start_date'); ?>" autocomplete="off" style="width: 100%;" />' +
                    '</div>' +
                    '<div class="col-md-2">' +
                        '<input type="text" class="dates milestoneDateTo" name="milestones[' + milestoneIndex + '][editTo]" placeholder="<?php echo $tpl->__('label.planned_end_date'); ?>" autocomplete="off" style="width: 100%;" />' +
                    '</div>' +
                    '<div class="col-md-3">' +
                        '<input type="number" name="milestones[' + milestoneIndex + '][weight]" placeholder="<?php echo $tpl->__('label.milestone_weight'); ?>" min="1" max="100" style="width: 100%;" />' +
                    '</div>' +
                    '<div class="col-md-1">' +
                        '<button type="button" class="btn btn-default removeMilestone" data-tippy-content="<?php echo $tpl->__('buttons.delete'); ?>"><i class="fa fa-trash"></i></button>' +
                    '</div>' +
                '</div>';

            jQuery("#projectMilestones").append(row);
            milestoneIndex++;
            leantime.dateController.initDateRangePicker(".milestoneDateFrom", ".milestoneDateTo", 2);
            updateRemoveButtons();
        });

        jQuery("#projectMilestones").on("click", ".removeMilestone", function() {
            if (jQuery("#projectMilestones .project-milestone-row").length > 1) {
                jQuery(this).closest(".project-milestone-row").remove();
            }
            updateRemoveButtons();
        });

        updateRemoveButtons();

        leantime.projectsController.initProjectTabs();
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        }
    );

</script>
