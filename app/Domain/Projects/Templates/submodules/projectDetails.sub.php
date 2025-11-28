<?php

use Leantime\Domain\Menu\Repositories\Menu;

defined('RESTRICTED') or exit('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
$menuTypes = $tpl->get('menuTypes');

?>


<form action="" method="post" class="stdform">

    <div class="row">

        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $tpl->e($project['name']) ?>" placeholder="<?= $tpl->__('input.placeholders.enter_title_of_project')?>"/>
                    </div>
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
                               value="<?php $tpl->e($project['projectKey'] ?? $project['project_key'] ?? '') ?>" 
                               placeholder="<?= $tpl->__('input.placeholders.enter_project_key') ?>"/>
                        <small style="display: block; margin-top: 5px; color: #666;">
                            <?php echo $tpl->__('label.project_key_description'); ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p>
                        <?php echo $tpl->__('label.accomplish'); ?>
                        <br /><br />
                    </p>
                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo htmlentities($project['details']) ?></textarea>
                </div>
            </div>
            <script>
            // Force uppercase for project key
            jQuery(document).ready(function() {
                var projectKeyInput = jQuery('#projectKey');
                
                // Force uppercase and remove special characters
                projectKeyInput.on('input', function() {
                    var val = jQuery(this).val();
                    jQuery(this).val(val.toUpperCase().replace(/[^A-Z0-9]/g, ''));
                });
            });
            </script>
            <div class="row padding-top">
                <div class="col-md-12">

                    <input type="submit" name="save" id="save" class="button" value="<?php echo $tpl->__('buttons.save'); ?>" class="button" />
                </div>

            </div>
        </div>
        <div class="col-md-4">

            <div class="row marginBottom">



                <?php if ($tpl->get('projectTypes') && count($tpl->get('projectTypes')) > 1) {?>
                <div class="col-md-12 center">
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
                </div>
                <?php } ?>


            </div>
            <div class="row marginBottom">

                <div class="col-md-12 center">

                    <h4 class="widgettitle title-light"><span
                            class="fa fa-picture-o"></span><?php echo $tpl->__('label.project_avatar'); ?></h4>

                    <img src='<?= BASE_URL?>/api/projects?projectAvatar=<?= $project['id']; ?>&v=<?= format($project['modified'])->timestamp() ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
                    <div id="projectAvatar">
                    </div>

                    <div class="par">

                        <div class='fileupload fileupload-new' data-provides='fileupload'>
                            <input type="hidden"/>
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="fa-file fileupload-exists"></i>
                                    <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                        <span class="fileupload-new"><?php echo $tpl->__('buttons.select_file') ?></span>
                                        <span class='fileupload-exists'><?php echo $tpl->__('buttons.change') ?></span>
                                        <input type='file' name='file' onchange="leantime.projectsController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.projectsController.clearCroppie()"><?php echo $tpl->__('buttons.remove') ?></a>
                            </div>

                            <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                <span onclick="leantime.projectsController.saveCroppie()"><?php echo $tpl->__('buttons.save') ?></span>
                                <span class="ld ld-ring ld-spin"></span>
                            </span>
                        <input type="hidden" name="profileImage" value="1" />
                        <input id="picSubmit" type="submit" name="savePic" class="hidden"
                               value="<?php echo $tpl->__('buttons.upload'); ?>"/>

                        </div>
                    </div>
                </div>

            </div>

                <?php $tpl->dispatchTplEvent('afterProjectAvatar', $project) ?>

                <div class="row marginBottom" style="margin-bottom: 30px;">
                    <div class="col-md-12">
                        <h4 class="widgettitle title-light"><span
                                class="fa fa-calendar"></span><?php echo $tpl->__('label.project_dates'); ?></h4>


                        <label class="control-label"><?php echo $tpl->__('label.project_start'); ?></label>
                        <div class="">
                            <input type="text" class="dates" style="width:100px;" name="start" autocomplete="off"
                                   value="<?php echo !empty($project['start']) ? format($project['start'])->date() : ''; ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>

                        </div>
                        <label class="control-label"><?php echo $tpl->__('label.project_end'); ?></label>
                        <div class="">
                            <input type="text" class="dates" style="width:100px;" name="end" autocomplete="off"
                                   value="<?php echo !empty($project['end']) ? format($project['end'])->date() : ''; ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>

                        </div>
                    </div>

                </div>


                <div class="row" style="margin-bottom: 30px;">

                    <div class="col-md-12 " style="margin-bottom: 30px;">
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



            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12">
                    <h4 class="widgettitle title-light"><span
                            class="fa fa-wrench"></span><?php echo $tpl->__('label.settings'); ?></h4>

            <input type="hidden" name="menuType" id="menuType"
                           value="<?php echo Menu::DEFAULT_MENU; ?>">

                    <div class="form-group">

                <label class="col-md-4 control-label" for="projectState"><?php echo $tpl->__('label.project_state'); ?></label>
                <div class="col-md-6">
                    <select name="projectState" id="projectState">
                        <option value="0" <?php if ($project['state'] == 0) {
                            ?> selected=selected
                                          <?php } ?>><?php echo $tpl->__('label.open'); ?></option>

                        <option value="-1" <?php if ($project['state'] == -1) {
                            ?> selected=selected
                                           <?php } ?>><?php echo $tpl->__('label.closed'); ?></option>

                    </select>
                </div>
            </div>

                </div>
            </div>

            <div class="row marginBottom" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
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

            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-12 ">
                    <h4 class="widgettitle title-light"><span
                                class="fa fa-money-bill-alt"></span><?php echo $tpl->__('label.budgets'); ?></h4>
                    <div class="form-group">
                        <label class="col-md-4 control-label"for="hourBudget"><?php echo $tpl->__('label.hourly_budget'); ?></label>
                        <div class="col-md-6">
                            <input type="text" name="hourBudget" class="input-large" id="hourBudget" value="<?php $tpl->e($project['hourBudget']) ?>" />

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label" for="dollarBudget"><?php echo $tpl->__('label.budget_cost'); ?></label>
                        <div class="col-md-6">
                            <input type="text" name="dollarBudget" class="input-large" id="dollarBudget" value="<?php $tpl->e($project['dollarBudget']) ?>" />

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>


</form>
