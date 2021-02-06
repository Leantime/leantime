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
                    <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span><?=$this->__('label.general'); ?></h4>

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

                            <?php foreach($this->get('clients') as $row){ ?>
                                <option value="<?php echo $row['id']; ?>"
                                    <?php if($project['clientId'] == $row['id']) { ?> selected=selected
                                    <?php } ?>><?php $this->e($row['name']); ?></option>
                            <?php } ?>

                            </select>
                            <?php if($login::userIsAtLeast("manager")) { ?>
                            <a href="<?=BASE_URL?>/clients/newClient" target="_blank"><?=$this->__('label.client_not_listed'); ?></a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group">

                        <label class="span4 control-label" for="projectState"><?php echo $this->__('label.project_state'); ?></label>
                        <div class="span6">
                            <select name="projectState" id="projectState">
                                <option value="0" <?php if($project['state'] == 0) { ?> selected=selected
                                <?php } ?>><?php echo $this->__('label.open'); ?></option>

                                <option value="-1" <?php if($project['state'] == -1) { ?> selected=selected
                               <?php } ?>><?php echo $this->__('label.closed'); ?></option>

                            </select>

                        </div>
                    </div>

                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light">
                        <span class="iconfa iconfa-asterisk"></span><?php echo $this->__('label.description'); ?>
                    </h4>
                    <textarea name="details" id="details" class="projectTinymce" rows="5" cols="50"><?php echo $project['details'] ?></textarea>

                </div>
            </div>
        </div>
        <div class="span4">
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-group"></span><?php echo $this->__('label.team_members'); ?></h4>
                    <div class="form-group">
                        <?php echo $this->__('text.choose_access_for_users'); ?><br />
                        <a href='<?=BASE_URL?>/users/showAll/'><?php echo $this->__('links.dont_do_it_alone'); ?></a>
                        <br /><br />

                        <div class="assign-container">
                            <?php foreach($this->get('availableUsers') as $row){ ?>

                                    <p class="half">
                                        <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>'
                                            <?php if(in_array($row['id'], $project['assignedUsers'])) : ?> checked="checked"<?php
                                            endif; ?>/>

                                        <label for="user-<?php echo $row['id'] ?>"><?php printf( $this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></label>
                                    </p>
                            <?php } ?>
                        </div>
                    </div>


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

	<div class="row-fluid  padding-top">
		<h4 class="widgettitle title-light">
			<span class="iconfa iconfa-ambulance"></span><?php echo $this->__('label.additional_settings'); ?>
		</h4>
	</div>

	<div class="row-fluid  padding-top">
		<div class="span8">
			<div class="form-group">

				<label class="span4 control-label" for="ticketLayout"><?php echo $this->__('label.ticket_layout'); ?></label>
				<div class="span6">
					<div class="span6">
						<select name="settingsTicketLayout" id="ticketLayout">
							<option value="0" <?php if( $_SESSION["projectsettings"]['ticketLayout'] == 0) { ?> selected=selected
							<?php } ?>><?php echo $this->__('label.classic'); ?></option>

							<option value="1" <?php if( $_SESSION["projectsettings"]['ticketLayout'] == 1) { ?> selected=selected
							<?php } ?>><?php echo $this->__('label.oneView'); ?></option>

						</select>

					</div>
				</div>
			</div>
		</div>
		<div class="span4">
			<div class="form-group">
				<label class="span4 control-label" for="commentOrder"><?php echo $this->__('label.comment_order'); ?></label>
				<div class="span6">
					<div class="span6">
						<select name="settingsCommentOrder" id="commentOrder">
							<option value="0" <?php if( $_SESSION["projectsettings"]['commentOrder'] == "0") { ?> selected=selected
							<?php } ?>><?php echo $this->__('label.uptodown'); ?></option>

							<option value="1" <?php if( $_SESSION["projectsettings"]['commentOrder'] == "1") { ?> selected=selected
							<?php } ?>><?php echo $this->__('label.downtoup'); ?></option>

						</select>

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
