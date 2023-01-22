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
                    <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span><?=$this->__('label.general'); ?></h4>

                    <div class="form-group">

                        <label  class="span4 control-label" for="name"><?=$this->__('label.name'); ?></label>
                        <div class="span6">
                            <input type="text" name="name" id="name" class="input-large" value="<?php $this->e($project['name']) ?>" />

                        </div>
                    </div>

                    <div class="form-group">

                        <label  class="span4 control-label" for="clientId"><?=$this->__('label.client_product'); ?></label>
                        <div class="span6">
                            <select name="clientId" id="clientId">

                            <?php foreach ($this->get('clients') as $row) { ?>
                                <option value="<?php echo $row['id']; ?>"
                                    <?php if ($project['clientId'] == $row['id']) {
                                        ?> selected=selected
                                    <?php } ?>><?php $this->e($row['name']); ?></option>
                            <?php } ?>

                            </select>
                            <?php if ($login::userIsAtLeast("manager")) { ?>
                            <a href="<?=BASE_URL?>/clients/newClient" target="_blank"><?=$this->__('label.client_not_listed'); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if ($config->enableMenuType) {?>
                        <div class="form-group">

                            <label class="span4 control-label" for="menuType"><?php echo $this->__('label.menu_type'); ?></label>
                            <div class="span6">
                                <select name="menuType" id="menuType">
                                    <?php foreach ($menuTypes as $key => $menu) { ?>
                                       <option value="<?=$key ?>" <?=$project['menuType'] == $key ? "selected='selected'" : ''?>><?php echo $this->__("label.menu_type.$key"); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" name="menuType" id="menuType"
                               value="<?php echo \leantime\domain\repositories\menu::DEFAULT_MENU; ?>">
                    <?php } ?>
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
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light">
                        <span class="fa fa-asterisk"></span><?php echo $this->__('label.description'); ?>
                    </h4>
                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo $project['details'] ?></textarea>

                </div>
            </div>
        </div>
        <div class="span4">

            <div class="row-fluid">
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
                <div class="span12 padding-top">
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
