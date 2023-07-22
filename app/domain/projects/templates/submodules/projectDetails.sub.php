<?php
defined('RESTRICTED') or die('Restricted access');

$project = $this->get('project');
$menuTypes = $this->get('menuTypes');

?>


<form action="" method="post" class="stdform">

    <div class="row-fluid">

        <div class="span8">
            <div class="row-fluid">
                <div class="span12">

                    <div class="form-group">

                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $this->e($project['name']) ?>" placeholder="<?=$this->__('input.placeholders.enter_title_of_project')?>"/>

                    </div>



                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <p>
                        <?php echo $this->__('label.accomplish'); ?><br />
                    </p>
                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo htmlentities($project['details']) ?></textarea>

                </div>
            </div>
        </div>
        <div class="span4">

            <div class="row-fluid marginBottom">

                <?php if($this->get('projectTypes') && count($this->get('projectTypes')) > 1) {?>
                    <h4 class="widgettitle title-light"><i class="fa-regular fa-rectangle-list"></i> Project Type</h4>
                    <p>The type of the project. This will determine which features are available.</p>
                    <select name="type">
                        <?php foreach($this->get('projectTypes') as $key => $type){ ?>
                            <option value="<?=$this->escape($key)?>"
                            <?php if($project['type'] == $key) echo " selected='selected' "; ?>
                            ><?=$this->__($this->escape($type))?></option>
                        <?php } ?>
                    </select>
                    <br /><br />
                <?php } ?>


                <h4 class="widgettitle title-light"><span
                        class="fa fa-picture-o"></span><?php echo $this->__('label.project_avatar'); ?></h4>
                <div class="span12 center">



                    <img src='<?=BASE_URL?>/api/projects?projectAvatar=<?=$project['id']; ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
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
                                        <span class="fileupload-new"><?php echo $this->__('buttons.select_file') ?></span>
                                        <span class='fileupload-exists'><?php echo $this->__('buttons.change') ?></span>
                                        <input type='file' name='file' onchange="leantime.projectsController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                                <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.projectsController.clearCroppie()"><?php echo $this->__('buttons.remove') ?></a>
                            </div>

                            <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                <span onclick="leantime.projectsController.saveCroppie()"><?php echo $this->__('buttons.save') ?></span>
                                <span class="ld ld-ring ld-spin"></span>
                            </span>
                        <input type="hidden" name="profileImage" value="1" />
                        <input id="picSubmit" type="submit" name="savePic" class="hidden"
                               value="<?php echo $this->__('buttons.upload'); ?>"/>

                        </div>
                    </div>
                </div>


                <?php $this->dispatchTplEvent("afterProjectAvatar", $project) ?>

                <div class="row-fluid marginBottom">
                    <h4 class="widgettitle title-light"><span
                            class="fa fa-calendar"></span><?php echo $this->__('label.project_dates'); ?></h4>

                    <label class="span4 control-label"><?php echo $this->__('label.project_start'); ?></label>
                    <div class="span6">
                        <input type="text" class="dates" style="width:90px;" name="start" autocomplete="off"
                               value="<?php echo $this->getFormattedDateString($project['start']); ?>" placeholder="<?=$this->__('language.dateformat') ?>"/>

                    </div>
                    <label class="span4 control-label"><?php echo $this->__('label.project_end'); ?></label>
                    <div class="span6">
                        <input type="text" class="dates" style="width:90px;" name="end" autocomplete="off"
                               value="<?php echo $this->getFormattedDateString($project['end']); ?>" placeholder="<?=$this->__('language.dateformat') ?>"/>

                    </div>

                </div>



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
                            class="fa fa-wrench"></span><?php echo $this->__('label.settings'); ?></h4>

            <input type="hidden" name="menuType" id="menuType"
                           value="<?php echo \leantime\domain\repositories\menu::DEFAULT_MENU; ?>">

                    <div class="form-group">

                <label class="span4 control-label" for="projectState"><?php echo $this->__('label.project_state'); ?></label>
                <div class="span6">
                    <select name="projectState" id="projectState">
                        <option value="0" <?php if ($project['state'] == 0) {
                            ?> selected=selected
                        <?php } ?>><?php echo $this->__('label.open'); ?></option>

                        <option value="-1" <?php if ($project['state'] == -1) {
                            ?> selected=selected
                        <?php } ?>><?php echo $this->__('label.closed'); ?></option>

                    </select>
                </div>
            </div>

                </div>
            </div>

            <div class="row-fluid marginBottom">
                <div class="span12 ">
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

            <div class="row-fluid">
                <div class="span12 ">
                    <h4 class="widgettitle title-light"><span
                                class="fa fa-money-bill-alt"></span><?php echo $this->__('label.budgets'); ?></h4>
                    <div class="form-group">
                        <label class="span4 control-label"for="hourBudget"><?php echo $this->__('label.hourly_budget'); ?></label>
                        <div class="span6">
                            <input type="text" name="hourBudget" class="input-large" id="hourBudget" value="<?php $this->e($project['hourBudget']) ?>" />

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label" for="dollarBudget"><?php echo $this->__('label.budget_cost'); ?></label>
                        <div class="span6">
                            <input type="text" name="dollarBudget" class="input-large" id="dollarBudget" value="<?php $this->e($project['dollarBudget']) ?>" />

                        </div>
                    </div>

                </div>
            </div>

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
</form>
