<?php
defined('RESTRICTED') or die('Restricted access');

$project = $this->get('project');
$helper = $this->get('helper');
?>


<form action="" method="post" class="stdform">

    <div class="row-fluid">

        <div class="span8">
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>General</h4>

                    <div class="form-group">

                        <label  class="span4 control-label" for="name"><?php echo $language->lang_echo('NAME'); ?></label>
                        <div class="span6">
                            <input type="text" name="name" id="name" class="input-large" value="<?php echo $project['name'] ?>" />

                        </div>
                    </div>

                    <div class="form-group">

                        <label  class="span4 control-label" for="clientId">Client/Product</label>
                        <div class="span6">
                            <select name="clientId" id="clientId">

                            <?php foreach($this->get('clients') as $row){ ?>
                                <option value="<?php echo $row['id']; ?>"
                                    <?php if($project['clientId'] == $row['id']) { ?> selected=selected
                                    <?php } ?>><?php echo $row['name']; ?></option>
                            <?php } ?>

                            </select>
                            <a href="<?=BASE_URL ?>/clients/newClient" target="_blank">Client not listed? Create a new one.</a>
                        </div>
                    </div>

                    <div class="form-group">

                        <label class="span4 control-label" for="projectState"><?php echo $language->lang_echo('PROJECTAPPROVAL'); ?></label>
                        <div class="span6">
                            <select name="projectState" id="projectState">
                                <option value="0" <?php if($project['state'] == 0) { ?> selected=selected
                                <?php } ?>><?php echo $language->lang_echo('OPEN'); ?></option>

                                <option value="-1" <?php if($project['state'] == -1) { ?> selected=selected
                               <?php } ?>><?php echo $language->lang_echo('CLOSED'); ?></option>

                            </select>

                        </div>
                    </div>

                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-asterisk"></span><?php echo $language->lang_echo('DESCRIPTION'); ?>
                    </h4>

                    <textarea name="details" id="details" class="tinymce" rows="5" cols="50"><?php echo $project['details'] ?></textarea>

                </div>
            </div>
        </div>
        <div class="span4">
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-group"></span>Team Members</h4>
                    <div class="form-group">
                        Choose the users that will have access to this project. <a href="<?=BASE_URL ?>/users/showAll/">Add more users here</a><br />

                        <div class="assign-container">
                            <?php foreach($this->get('availableUsers') as $row){ ?>
                                <?php if($_SESSION['userdata']['role'] == "user") { ?>

                                    <?php if(in_array($row['id'], explode(',', $project['editorId'])) || ($row['id'] == $_SESSION['userdata']["id"])) { ?>
                                        <p class="half">

                                            <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>'
                                                <?php if(in_array($row['id'], $project['assignedUsers'])) : ?> checked="checked"<?php 
                                                endif; ?>/>

                                            <label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>
                                        </p>
                                    <?php } ?>
                                <?php }else{ ?>

                                    <p class="half">
                                        <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>'
                                            <?php if(in_array($row['id'], $project['assignedUsers'])) : ?> checked="checked"<?php 
                                            endif; ?>/>

                                        <label for="user-<?php echo $row['id'] ?>"><?php echo $row['lastname'] .', '. $row['firstname'] ?></label>
                                    </p>
                                <?php } ?>

                            <?php } ?>
                        </div>
                    </div>


                </div>
            </div>
            <div class="row-fluid">
                <div class="span12 padding-top">
                    <h4 class="widgettitle title-light"><span
                                class="fa fa-dollar-sign"></span>Budgets</h4>
                    <div class="form-group">
                        <label class="span4 control-label"for="hourBudget">Hour Budget</label>
                        <div class="span6">
                            <input type="text" name="hourBudget" class="input-large" id="hourBudget" value="<?php echo $project['hourBudget'] ?>" />

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label" for="dollarBudget">Dollar Budget</label>
                        <div class="span6">
                            <input type="text" name="dollarBudget" class="input-large" id="dollarBudget" value="<?php echo $project['dollarBudget'] ?>" />

                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
    <div class="row-fluid padding-top">
        <?php if ($project['id'] != '') : ?>
            <div class="pull-right padding-top">
                <a href="<?=BASE_URL ?>/projects/delProject/<?php echo $project['id']?>" class="delete"><i class="fa fa-trash"></i> Delete</a>

            </div>
        <?php endif; ?>

        <input type="submit" name="save" id="save" class="button" value="<?php echo $language->lang_echo('SAVE'); ?>" class="button" />

    </div>
</form>
