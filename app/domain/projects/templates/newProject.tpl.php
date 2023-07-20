
<?php
 defined('RESTRICTED') or die('Restricted access');
$project = $this->get('project');

?>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?=BASE_URL ?>/projects/showAll" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> <?php echo $this->__('links.go_back') ?></a>
    </div>

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo $this->__('headline.new_project') ?></h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><?php echo $this->__('tabs.projectdetails'); ?></a></li>
            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="stdform">

                    <div class="row-fluid">

                        <div class="span8">
                            <div class="row-fluid">
                                <div class="span12">

                                    <div class="form-group">

                                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $this->e($project['name']) ?>" placeholder="<?=$this->__('input.placeholders.enter_title_of_project')?>"/>

                                    </div>


                                    <input type="hidden" name="projectState"  id="projectState" value="0" />


                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <p>
                                        <?php echo $this->__('label.accomplish'); ?>
                                    </p>
                                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo $project['details'] ?></textarea>

                                </div>
                            </div>
                            <div class="row-fluid padding-top">
                                <?php if ($project['id'] != '') : ?>
                                    <div class="pull-right padding-top">
                                        <a href="<?=BASE_URL?>/projects/delProject/<?php echo $project['id']?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__('buttons.delete'); ?></a>
                                    </div>
                                <?php endif; ?>
                                <input type="submit" name="save" id="save" class="button" value="<?php echo $this->__('buttons.save'); ?>" class="button" />

                            </div>
                        </div>

                        <div class="span4">

                            <?php $this->dispatchTplEvent("beforeClientPicker", $project) ?>

                            <div class="row-fluid marginBottom">
                                <h4 class="widgettitle title-light"><span
                                        class="fa fa-calendar"></span><?php echo $this->__('label.project_dates'); ?></h4>

                                <label class="span4 control-label"><?php echo $this->__('label.project_start'); ?></label>
                                <div class="span6">
                                    <input type="text" class="dates" style="width:90px;" name="start" autocomplete="off"
                                           value="<?php echo $project['start']; ?>" placeholder="<?=$this->__('language.dateformat') ?>"/>

                                </div>
                                <label class="span4 control-label"><?php echo $this->__('label.project_end'); ?></label>
                                <div class="span6">
                                    <input type="text" class="dates" style="width:90px;" name="end" autocomplete="off"
                                           value="<?php echo $project['end']; ?>" placeholder="<?=$this->__('language.dateformat') ?>"/>

                                </div>

                            </div>

                            <div class="row-fluid marginBottom">

                                <div class="span12 ">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span><?php echo $this->__('label.client_product'); ?></h4>
                                    <select name="clientId" id="clientId">

                                        <?php foreach ($this->get('clients') as $row) { ?>
                                            <option value="<?php echo $row['id']; ?>"
                                                <?php if ($project['clientId'] == $row['id']) {
                                                    ?> selected=selected
                                                <?php } ?>><?php $this->e($row['name']); ?></option>
                                        <?php } ?>

                                    </select>
                                    <?php if ($login::userIsAtLeast("manager")) { ?>
                                        <br /><a href="<?=BASE_URL?>/clients/newClient" target="_blank"><?=$this->__('label.client_not_listed'); ?></a>
                                    <?php } ?>


                                </div>
                            </div>

                            <div class="row-fluid marginBottom">
                                <div class="span12">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span><?php echo $this->__('labels.defaultaccess'); ?></h4>
                                    <?php echo $this->__('text.who_can_access'); ?>
                                    <br /><br />

                                    <select name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" <?=$project['psettings'] == "restricted" ? "selected='selected'" : '' ?>><?php echo $this->__("labels.only_chose"); ?></option>
                                        <option value="clients" <?=$project['psettings'] == "clients" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_client"); ?></option>
                                        <option value="all" <?=$project['psettings'] == "all" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_org"); ?></option>
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
        jQuery(".dates").datepicker(
            {
                dateFormat:  leantime.i18n.__("language.jsdateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            }
        );


            leantime.projectsController.initProjectTabs();
            leantime.projectsController.initProjectsEditor();

        }
    );

</script>
