
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
                                    <div class="form-group">
                                        <label for="projectKey" style="font-weight: 600; display: block; margin-bottom: 5px;">
                                            <?php echo $tpl->__('label.project_key'); ?>
                                            <span style="font-weight: normal; font-size: 0.9em; color: #666;">(2-10 characters, letters and numbers only)</span>
                                        </label>
                                        <input type="text" name="projectKey" id="projectKey" maxlength="10" style="width:150px; text-transform: uppercase;" 
                                               value="<?php $tpl->e($project['projectKey'] ?? '') ?>" 
                                               placeholder="<?= $tpl->__('input.placeholders.auto_generated') ?>"/>
                                        <small style="display: block; margin-top: 5px; color: #666;">
                                            <?php echo $tpl->__('label.project_key_description'); ?>
                                        </small>
                                    </div>
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
                                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo htmlentities($project['details']) ?></textarea>

                                </div>
                            </div>
                            <script>
                            // Auto-generate project key from project name
                            jQuery(document).ready(function() {
                                var projectKeyInput = jQuery('#projectKey');
                                var projectNameInput = jQuery('#name');
                                var userModifiedKey = false;

                                // Track if user manually edited the key
                                projectKeyInput.on('input', function() {
                                    if (jQuery(this).val().length > 0) {
                                        userModifiedKey = true;
                                    }
                                });

                                // Auto-generate key when name changes (if user hasn't modified it)
                                projectNameInput.on('blur', function() {
                                    if (!userModifiedKey || projectKeyInput.val().length === 0) {
                                        var name = jQuery(this).val();
                                        if (name.length > 0) {
                                            // Generate key from first letters of each word
                                            var words = name.replace(/[^a-zA-Z0-9\s]/g, '').trim().split(/\s+/);
                                            var key = '';
                                            words.forEach(function(word) {
                                                if (word.length > 0) {
                                                    key += word.charAt(0).toUpperCase();
                                                }
                                            });
                                            // If key is too short, use first 2-3 chars of name
                                            if (key.length < 2) {
                                                key = name.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();
                                            }
                                            // Limit to 10 characters
                                            key = key.substring(0, 10);
                                            projectKeyInput.val(key);
                                        }
                                    }
                                });

                                // Force uppercase
                                projectKeyInput.on('input', function() {
                                    var val = jQuery(this).val();
                                    jQuery(this).val(val.toUpperCase().replace(/[^A-Z0-9]/g, ''));
                                });
                            });
                            </script>
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

        leantime.projectsController.initProjectTabs();
        leantime.editorController.initComplexEditor();

        }
    );

</script>
