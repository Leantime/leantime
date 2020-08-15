<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
$currentLink = $this->get('current');
$module = '';
$action = '';

if (is_array($currentLink)) {
	$module = $currentLink[0];
	$action = $currentLink[1];
}
//Set Admin Links...
$adminLinks = array("setting", "users", "projects", "clients", "calendar");

?>

<ul class="nav nav-tabs nav-stacked">
	<?php if ((in_array($module, $adminLinks) && $action != 'showProject')|| ($module == 'timesheets' && $action == 'showMy')) { ?>
		<li class="dropdown"  style="margin-top:67px;">
			<ul style='display:block'>
				<li class="nav-header border"><?= $this->__("label.settings") ?></li>
				<li  <?php if ($action == 'editOwn' && $module == 'users') echo " class='active' "; ?>>
					<a href='<?= BASE_URL ?>/users/editOwn/'>
						<?= $this->__("menu.my_profile") ?>
					</a>
				</li>
				<li <?php if ($action == 'showMy' && $module == 'projects') echo " class='active' "; ?>>
					<a href='<?= BASE_URL ?>/projects/showMy'>
						<?= $this->__("menu.my_projects") ?>
					</a>
				</li>
				<li  <?php if ($module == 'timesheets' && $action == 'showMy') echo " class='active' "; ?>>
					<a href='<?= BASE_URL ?>/timesheets/showMy/'>
						<?= $this->__("menu.my_timesheets_menu") ?>
					</a>
				</li>

				<li <?php if ($module == 'calendar') echo " class='active' "; ?>>
					<a href='<?=BASE_URL ?>/calendar/showMyCalendar'>
						<?=$this->__("menu.my_calendar_menu")?>
					</a>
				</li>

				<?php if ($login::userIsAtLeast("clientManager")) { ?>

					<li class="nav-header border"><?= $this->__("label.administration") ?></li>

					<li <?php if ($action == 'showAll' && $module == 'projects') echo " class='active' "; ?>>
						<a href='<?= BASE_URL ?>/projects/showAll/'>
							<?= $this->__("menu.all_projects") ?>
						</a>
					</li>

					<li <?php if ($module == 'clients') echo " class='active' "; ?>>
						<a href='<?= BASE_URL ?>/clients/showAll/'>
							<?= $this->__("menu.all_clients") ?>
						</a>
					</li>
					<li <?php if (($module == 'users' && $action == 'showAll') || $action == 'newUser'|| $action == 'editUser') echo " class='active' "; ?>>
						<a href='<?= BASE_URL ?>/users/showAll/'>
							<?= $this->__("menu.all_users") ?>
						</a>
					</li>

					<?php if ($login::userIsAtLeast("admin")) { ?>
						<li <?php if ($module == 'setting') echo " class='active' "; ?>>
							<a href='<?= BASE_URL ?>/setting/editCompanySettings/'>
								<?= $this->__("menu.company_settings") ?>
							</a>
						</li>
					<?php } ?>
					<li class="nav-header border"><?= $this->__("menu.help_support") ?></li>
					<!--<li>
						<a href='javascript:void(0);'
						   onclick="leantime.helperController.showHelperModal('<?php echo $this->get('modal'); ?>');">
							<?= $this->__("menu.show_me_around") ?>
						</a>
					</li>-->
					<li>
						<a href='http://docs.leantime.io' target="_blank">
							<?= $this->__("menu.knowledge_base") ?>
						</a>
					</li>
					<li>
						<a href='http://community.leantime.io' target="_blank">
							<?= $this->__("menu.community") ?>
						</a>
					</li>
					<li>
						<a href='https://leantime.io/contact-us'
						   target="_blank">
							<?= $this->__("menu.contact_us") ?>
						</a>
					</li>
					<li class="border">
						<a href='<?= BASE_URL ?>/index.php?logout=1'>
							<?= $this->__("menu.sign_out") ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</li>
	<?php } else {
		?>
		<?php if ($this->get('allProjects') !== false) { ?>
			<li class="project-selector">

				<div class="form-group">
					<form action="" method="post">
						<a href="javascript:void(0)"
						   class="dropdown-toggle bigProjectSelector"
						   data-toggle="dropdown">
							<?php $this->e($_SESSION['currentProjectName']); ?>
							&nbsp;<i class="fa fa-caret-down"></i>
						</a>

						<ul class="dropdown-menu projectselector">
							<li class="intro">
								<span class="sub"><?= $this->__("menu.current_project") ?></span><br/>
								<span class="title"><?php $this->e($_SESSION['currentProjectName']); ?></span>
							</li>

							<?php
							$lastClient = "";

							if (count($this->get('allProjects')) > 1) {
								foreach ($this->get('allProjects') as $projectRow) {

									if ($lastClient != $projectRow['clientName']) {
										$lastClient = $projectRow['clientName'];
										echo "<li class='nav-header border openToggle' onclick='leantime.menuController.toggleClientList(" . $projectRow['clientId'] . ", this)'>" . $this->escape($projectRow['clientName']) . " <i class=\"fa fa-caret-down\"></i></li>";
									}
									echo "<li class='client_" . $projectRow['clientId'] . "";
									if ($this->get('currentProject') == $projectRow["id"]) {
										echo " active ";
									}
									echo "'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "'>" . $this->escape($projectRow["name"]) . "</a></li>";
								}
							} else {
								echo "<li class='nav-header border'></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
							}
							?>
							<?php if ($login::userIsAtLeast("clientManager")) { ?>
								<li class='nav-header border'></li>
								<li>
									<a href="<?= BASE_URL ?>/projects/newProject/"><?= $this->__("menu.create_project") ?></a>
								</li>
								<li>
									<a href="<?= BASE_URL ?>/projects/showAll"><?= $this->__("menu.view_all_projects") ?></a>
								</li>
								<li>
									<a href="<?= BASE_URL ?>/clients/showAll"><?= $this->__("menu.view_all_clients") ?></a>
								</li>
							<?php } ?>
						</ul>
					</form>
				</div>
			</li>
			<li class="dropdown">
				<ul style='display:block'>
					<li <?php if ($module == 'dashboard') echo " class='active' "; ?>>
						<a href="<?= BASE_URL ?>/dashboard/show"><?= $this->__("menu.dashboard") ?></a>
					</li>
					<li <?php if ($module == 'tickets' && ($action == 'showKanban' || $action == 'showAll' || $action == 'showTicket')) echo "class=' active '"; ?>>
						<a href="<?= $this->get('ticketMenuLink'); ?>"><?= $this->__("menu.todos") ?></a>
					</li>
					<li <?php if ($module == 'tickets' && $action == 'roadmap') echo " class='active' "; ?>>
						<a href="<?= BASE_URL ?>/tickets/roadmap"><?= $this->__("menu.milestones") ?></a>
					</li>
					<li <?php if ($module == 'timesheets' && $action == 'showAll') echo " class='active' "; ?>>
						<a href="<?= BASE_URL ?>/timesheets/showAll"><?= $this->__("menu.timesheets") ?></a>
					</li>
					<li <?php if ($module == 'leancanvas') echo "  class='active' "; ?>>
						<a href="<?= BASE_URL ?>/leancanvas/simpleCanvas"><?= $this->__("menu.research") ?></a>
					</li>
					<li <?php if ($module == 'ideas') echo "  class='active' "; ?>>
						<a href="<?= BASE_URL ?>/ideas/showBoards"><?= $this->__("menu.ideas") ?></a>
					</li>
					<li <?php if ($module == 'retrospectives' && ($action == 'showBoards' || $action == 'showBoards')) echo "class=' active '"; ?>>
						<a href="<?= BASE_URL ?>/retrospectives/showBoards"><?= $this->__("menu.retrospectives") ?></a>
					</li>
					<li <?php if ($module == 'reports') echo "class=' active '"; ?>>
						<a href="<?= BASE_URL ?>/reports/show"><?= $this->__("menu.reports") ?></a>
					</li>
					<?php if ($login::userIsAtLeast("clientManager")) { ?>
						<li <?php if ($module == 'projects' && $action == 'showProject') echo "  class='active' "; ?>>
							<a href="<?= BASE_URL ?>/projects/showProject/<?= $_SESSION['currentProject'] ?>"><?= $this->__("menu.project_settings") ?></a>
						</li>
					<?php } ?>
				</ul>
			</li>
		<?php } ?>
	<?php } ?>
</ul>




