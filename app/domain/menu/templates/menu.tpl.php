<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
    $currentLink = $this->get('current');
    $module = '';
    $action = '';

    $redirectUrl = $this->incomingRequest->getRequestURI(BASE_URL);
    //Don't redirect if redirect goes to showProject.
    if(str_contains($redirectUrl, "showProject")) {
        $redirectUrl = "/dashboard/show";
    }

if (is_array($currentLink)) {
    $module = $currentLink[0] ?? '';
    $action = $currentLink[1] ?? '';
}
    $menuStructure = $this->get('menuStructure');

?>



<?php if (isset($_SESSION['currentProjectName'])) { ?>
    <?php $this->dispatchTplEvent('beforeMenu'); ?>
    <ul class="nav nav-tabs nav-stacked" id="expandedMenu"  <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'style="display:block;"'; else echo 'style="display:none;"'; ?>>
    <?php $this->dispatchTplEvent('afterMenuOpen'); ?>

    <?php if ($this->get('allAvailableProjects') !== false || $_SESSION['currentProject'] != "") {?>
    <li class="project-selector">

        <div class="form-group">
            <form action="" method="post">
                <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown">
                    <?php $this->e($_SESSION['currentProjectName']); ?>&nbsp;<i class="fa fa-caret-right"></i>
                </a>

                <?php $this->displaySubmodule('menu-projectSelector') ?>
            </form>
        </div>
    </li>
    <li class="dropdown">
        <?php $currentProjectType = $this->get('currentProjectType'); ?>
        <ul style='display:block;'>
            <?php foreach ($menuStructure as $key => $menuItem) { ?>
                <?php if ($menuItem['type'] == 'header') { ?>
                    <li><a href="javascript:void(0);"><strong><?=$this->__($menuItem['title']) ?></strong></a></li>
                <?php } ?>
                <?php if ($menuItem['type'] == 'separator') { ?>
                    <li class="separator"></li>
                <?php } ?>
                <?php if ($menuItem['type'] == 'item') { ?>
                     <li <?php if (($module == $menuItem['module']) &&  (!isset($menuItem['active']) || in_array($action, $menuItem['active']))) {
                            echo " class='active'";
                         } ?>>
                         <a href="<?=BASE_URL . $menuItem['href'] ?>"><?=$this->__($menuItem['title']) ?></a>
                     </li>
                <?php } ?>
                <?php if ($menuItem['type'] == 'submenu') { ?>
                    <li class="submenuToggle"><a href="javascript:<?php echo $menuItem['visual'] == 'always' ? 'void(0)' : 'leantime.menuController.toggleSubmenu(\'' . $menuItem['id'] . '\')'; ?>;"><i class="submenuCaret fa fa-angle-<?php echo $menuItem['visual'] == 'closed' ? 'right' : 'down'; ?>" id="submenu-icon-<?=$menuItem['id'] ?>"></i>  <strong><?=$this->__($menuItem['title']) ?></strong> </a></li>
                    <ul style="display: <?php echo $menuItem['visual'] == 'closed' ? 'none' : 'block'; ?>;" id="submenu-<?=$menuItem['id'] ?>" class="submenu">
                    <?php foreach ($menuItem['submenu'] as $subkey => $submenuItem) { ?>
                        <?php if ($submenuItem['type'] == 'header') { ?>
                            <li class="title"><a href="javascript:void(0);"><strong><?=$this->__($submenuItem['title']) ?></strong></a></li>
                        <?php } ?>
                        <?php if ($submenuItem['type'] == 'item') { ?>
                             <li <?php if ($module == $submenuItem['module'] && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))) {
                                    echo " class='active'";
                                 } ?>>
                                 <a href="<?=BASE_URL . $submenuItem['href'] ?>"><?=$this->__($submenuItem['title']) ?></a>
                             </li>
                        <?php } ?>
                    <?php } ?>
                    </ul>
                <?php } ?>
            <?php } ?>
            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
            <li <?php if ($module == 'projects' && $action == 'showProject') {
                echo"class='fixedMenuPoint active '";
                } else {
                    echo"class='fixedMenuPoint'";
                }?>>
                <a href="<?=BASE_URL ?>/projects/showProject/<?=$_SESSION['currentProject']?>"><?=$this->__("menu.project_settings") ?></a>
            </li>
            <?php } ?>
        </ul>

    </li>
    <?php } ?>
    <?php $this->dispatchTplEvent('beforeMenuClose'); ?>
</ul>

    <ul class="nav nav-tabs nav-stacked" id="minimizedMenu" <?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'style="display:block;"'; else echo 'style="display:none;"'; ?>>

        <?php $this->dispatchTplEvent('afterMenuOpen'); ?>
        <?php if ($this->get('allAvailableProjects') !== false || $_SESSION['currentProject'] != "") {?>
            <li class="project-selector">

                <div class="form-group">
                    <form action="" method="post">
                        <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown" data-tippy-content="<?php $this->e($_SESSION['currentProjectName']); ?>" data-tippy-placement="right"><i class="fa fa-briefcase"></i></a>

                        <?php $this->displaySubmodule('menu-projectSelector') ?>
                    </form>
                </div>
            </li>
            <li class="dropdown">
                <?php $currentProjectType = $this->get('currentProjectType'); ?>
                <ul style='display:block;'>
                    <?php foreach ($menuStructure as $key => $menuItem) { ?>
                        <?php if ($menuItem['type'] == 'separator') { ?>
                            <li class="separator"></li>
                        <?php } ?>
                        <?php if ($menuItem['type'] == 'item') { ?>
                            <li <?php if (($module == $menuItem['module']) &&  (!isset($menuItem['active']) || in_array($action, $menuItem['active']))) {
                                echo " class='active'";
                            } ?>>
                                <a href="<?=BASE_URL . $menuItem['href'] ?>" data-tippy-content="<?=$this->__($menuItem['tooltip']) ?>" data-tippy-placement="right"><span class="<?=$this->__($menuItem['icon']) ?>"></span></a>
                            </li>
                        <?php } ?>
                        <?php if ($menuItem['type'] == 'submenu') { ?>
                            <ul style="display:block;" id="submenu-<?=$menuItem['id'] ?>" class="submenu">
                                <?php foreach ($menuItem['submenu'] as $subkey => $submenuItem) { ?>

                                    <?php if ($submenuItem['type'] == 'item') { ?>
                                        <li <?php if ($module == $submenuItem['module'] && (!isset($submenuItem['active']) || in_array($action, $submenuItem['active']))) {
                                            echo " class='active'";
                                        } ?>>
                                            <a href="<?=BASE_URL . $submenuItem['href'] ?>" data-tippy-content="<?=$this->__($submenuItem['tooltip']) ?>" data-tippy-placement="right"><span class="<?=$this->__($submenuItem['icon']) ?>"></span></a>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                        <li <?php if ($module == 'projects' && $action == 'showProject') {
                            echo"class='fixedMenuPoint active '";
                        } else {
                            echo"class='fixedMenuPoint'";
                        }?>>
                            <a href="<?=BASE_URL ?>/projects/showProject/<?=$_SESSION['currentProject']?>" data-tippy-content="<?=$this->__("menu.project_settings_tooltip") ?>" data-tippy-placement="right"><span class="<?=$this->__("menu.project_settings_icon") ?>"></span></a>
                        </li>
                    <?php } ?>
                </ul>

            </li>
        <?php } ?>
        <?php $this->dispatchTplEvent('beforeMenuClose'); ?>
    </ul>

    <?php $this->dispatchTplEvent('afterMenuClose'); ?>

<?php } ?>

<script>
    jQuery('.projectSelectorTabs').tabs();


    let clientId = <?=$this->get('currentClient') ?>;
    console.log(jQuery(".clientId-"+clientId))
    leantime.menuController.toggleClientList(clientId, ".clientIdHead-"+clientId, "open");


    <?php
    $lastClient = "";

    if ($this->get('allAssignedProjects') !== false && count($this->get('allAssignedProjects')) >= 1) {
        foreach ($this->get('allAssignedProjects') as $projectRow) {
            if ($lastClient != $projectRow['clientName']) {
                $lastClient = $projectRow['clientName'];
                if(isset($_SESSION['submenuToggle']["clientDropdown-".$projectRow['clientId']])
                && $_SESSION['submenuToggle']["clientDropdown-".$projectRow['clientId']] == "open"){

                ?>
                    leantime.menuController.toggleClientList(<?=$projectRow['clientId']?>, ".clientIdHead-<?=$projectRow['clientId']?>", "open");

                <?php
                }
            }
        }
    }

    ?>

</script>



