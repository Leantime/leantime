<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
    $currentLink = $this->get('current');
    $module = '';
    $action = '';

    if(is_array($currentLink)) {

        $module = $currentLink[0]??'';
        $action = $currentLink[1]??'';
    }
    $menuStructure = $this->get('menuStructure');

?>

<?php if(isset($_SESSION['currentProjectName'])){ ?>

<?php $this->dispatchTplEvent('beforeMenu'); ?>
<ul class="nav nav-tabs nav-stacked">
    <?php $this->dispatchTplEvent('afterMenuOpen'); ?>
    <?php if ($this->get('allAvailableProjects') !== false || $_SESSION['currentProject'] != ""){?>
        <li class="project-selector">

            <div class="form-group">
                <form action="" method="post">
                    <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown">
                        <?php $this->e($_SESSION['currentProjectName']); ?>&nbsp;<i class="fa fa-caret-right"></i>
                    </a>

                    <ul class="dropdown-menu projectselector">
                        <li class="intro">
                            <span class="sub"><?=$this->__("menu.current_project") ?></span><br />
                            <span class="title"><?php $this->e($_SESSION['currentProjectName']); ?></span>
                        </li>

                        <?php
                        $lastClient = "";

                        if ($this->get('allAssignedProjects') !== false && count($this->get('allAssignedProjects')) >= 1) {
                            foreach ($this->get('allAssignedProjects') as $projectRow) {

                                if ($lastClient != $projectRow['clientName']) {
                                    $lastClient = $projectRow['clientName'];
                                    echo "<li class='nav-header border openToggle' onclick='leantime.menuController.toggleClientList(".$projectRow['clientId'].", this)'>" . $this->escape($projectRow['clientName']) . " <i class=\"fas fa-angle-down\"></i></li>";
                                }
                                echo "<li class='client_".$projectRow['clientId']."";
                                    if ($this->get('currentProject') == $projectRow["id"]) { echo " active "; }
                                echo"'><a href='".BASE_URL."/projects/changeCurrentProject/" . $projectRow["id"] . "'>" . $this->escape($projectRow["name"]) . "</a></li>";
                            }
                        } else {
                            echo "<li class='nav-header border'></li><li><span class='info'>".$this->__("menu.you_dont_have_projects")."</span></li>";
                        }
                        ?>
                        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                            <li class='nav-header border'></li>
                            <li><a href="<?=BASE_URL ?>/projects/newProject/"><?=$this->__("menu.create_project") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/projects/showAll"><?=$this->__("menu.view_all_projects") ?></a></li>
                        <?php } ?>
                        <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
                            <li><a href="<?=BASE_URL ?>/clients/showAll"><?=$this->__("menu.view_all_clients") ?></a></li>
                        <?php } ?>
                    </ul>
                </form>
            </div>
        </li>
    <li class="dropdown">
		<?php $currentProjectType = $this->get('currentProjectType'); ?>
		<ul style='display:block;'>
			<?php foreach($menuStructure as $key => $menuItem) { ?>
				<?php if($menuItem['type'] == 'header') { ?>
                    <li><a href="javascript:void(0);"><strong><?=$this->__($menuItem['title']) ?></strong></a></li>
				<?php } ?>
				<?php if($menuItem['type'] == 'item') { ?>
					 <li <?php if(($module == $menuItem['module']) &&  (!isset($menuItem['active']) || in_array( $action, $menuItem['active']))) echo " class='active'"; ?>>
                         <a href="<?=BASE_URL.$menuItem['href'] ?>"><?=$this->__($menuItem['title']) ?></a>
                     </li>
			    <?php } ?>
				<?php if($menuItem['type'] == 'submenu') { ?>
                    <li><a href="javascript:<?php echo $menuItem['visual'] == 'always' ? 'void(0)' : 'leantime.menuController.toggleSubmenu(\''.$menuItem['id'].'\')'; ?>;"><strong><?=$this->__($menuItem['title']) ?></strong> <i class="fa fa-angle-<?php echo $menuItem['visual'] == 'closed' ? 'up' : 'down'; ?>" id="submenu-icon-<?=$menuItem['id'] ?>"></i></a></li>
					<ul style="display: <?php echo $menuItem['visual'] == 'closed' ? 'none' : 'block'; ?>;" id="submenu-<?=$menuItem['id'] ?>">
					<?php foreach($menuItem['submenu'] as $subkey => $submenuItem) { ?>
				        <?php if($submenuItem['type'] == 'header') { ?>
                            <li><a href="javascript:void(0);" style="font-size: small; padding-top: 5px; padding-bottom: 5px"><strong><?=$this->__($submenuItem['title']) ?></strong></a></li>
				        <?php } ?>
				        <?php if($submenuItem['type'] == 'item') { ?>
							 <li <?php if($module == $submenuItem['module'] && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))) echo " class='active'"; ?>>
                                 <a href="<?=BASE_URL.$submenuItem['href'] ?>" style="font-size: small; padding-top: 5px; padding-bottom: 5px"><?=$this->__($submenuItem['title']) ?></a>
                             </li>
			            <?php } ?>
					<?php } ?>
					</ul>
				<?php } ?>
			<?php } ?>
            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
            <li <?php if($module == 'projects' && $action == 'showProject') echo"class='fixedMenuPoint active '";  else echo"class='fixedMenuPoint'";?>>
                <a href="<?=BASE_URL ?>/projects/showProject/<?=$_SESSION['currentProject']?>"><?=$this->__("menu.project_settings") ?></a>
            </li>
            <?php } ?>
        </ul>
        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
            <ul style='display:block'>
                <li <?php if($module == 'projects' && $action == 'showProject') echo"class=' active '"; ?> style="bottom:15px; position:fixed; width:240px; background: var(--secondary-background);">
                    <a href="<?=BASE_URL ?>/projects/showProject/<?=$_SESSION['currentProject']?>"><?=$this->__("menu.project_settings") ?></a>
                </li>
            </ul>
       <?php } ?>
    </li>
    <?php } ?>
    <?php $this->dispatchTplEvent('beforeMenuClose'); ?>
</ul>
<?php $this->dispatchTplEvent('afterMenuClose'); ?>

<?php } ?>



