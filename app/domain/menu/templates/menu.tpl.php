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


$currentProjectType = $this->get('currentProjectType');


$settingsLink = array(
    "label"=>$this->__("menu.project_settings"),
    "module"=>"projects",
    "action"=>"showProject",
    "settingsIcon"=>$this->__("menu.project_settings_icon"),
    "settingsTooltip"=>$this->__("menu.project_settings_tooltip") );
$settingsLink = $this->dispatchTplFilter('settingsLink', $settingsLink, array("type"=>$currentProjectType));

?>



<?php if (isset($_SESSION['currentProjectName'])) { ?>
    <?php $this->dispatchTplEvent('beforeMenu'); ?>
    <ul class="nav nav-tabs nav-stacked" id="expandedMenu">
    <?php $this->dispatchTplEvent('afterMenuOpen'); ?>

    <?php if ($this->get('allAvailableProjects') !== false || $_SESSION['currentProject'] != "") {?>
    <li class="project-selector">

        <div class="form-group">
            <form action="" method="post">
                <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown">
                    <span class='projectAvatar <?=$currentProjectType?>'>
                        <?php
                            if($currentProjectType == 'strategy') {
                                echo "<span class='fa fa-chess'></span>";
                            }else if($currentProjectType == 'program') {
                                echo "<span class='fa fa-layer-group'></span>";
                            }else{
                            ?>
                                <img src='<?=BASE_URL ?>/api/projects?projectAvatar=<?=$_SESSION['currentProject']?>' />
                            <?php } ?>
                    </span>
                    <?php $this->e($_SESSION['currentProjectName']); ?>&nbsp;<i class="fa fa-caret-right"></i>
                </a>

                <?php $this->displaySubmodule('menu-projectSelector') ?>
            </form>
        </div>
    </li>
    <li class="dropdown scrollableMenu">

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
            <li <?php if ($module == $settingsLink["module"] && $action == $settingsLink["action"]) {
                echo"class='fixedMenuPoint active '";
                } else {
                    echo"class='fixedMenuPoint'";
                }?>>
                <a href="<?=BASE_URL ?>/<?=$settingsLink["module"]?>/<?=$settingsLink["action"]?>/<?=$_SESSION['currentProject']?>"><?=$settingsLink["label"]?></a>
            </li>
            <?php } ?>
        </ul>

    </li>
    <?php } ?>
    <?php $this->dispatchTplEvent('beforeMenuClose'); ?>
</ul>

    <ul class="nav nav-tabs nav-stacked" id="minimizedMenu">

        <?php $this->dispatchTplEvent('afterMenuOpen'); ?>
        <?php if ($this->get('allAvailableProjects') !== false || $_SESSION['currentProject'] != "") {?>
            <li class="project-selector">

                <div class="form-group">
                    <form action="" method="post">
                        <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown" data-tippy-content="<?php $this->e($_SESSION['currentProjectName']); ?>" data-tippy-placement="right">
                            <span class='projectAvatar'><img src='<?=BASE_URL ?>/api/projects?projectAvatar=<?=$_SESSION['currentProject']?>' /></span>
                        </a>

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

                        <li <?php if ($module == $settingsLink["module"] && $action == $settingsLink["action"]) {
                            echo"class='fixedMenuPoint active '";
                        } else {
                            echo"class='fixedMenuPoint'";
                        }?>>
                            <a href="<?=BASE_URL ?>/<?=$settingsLink["module"] ?>/<?=$settingsLink["action"] ?>/<?=$_SESSION['currentProject']?>" data-tippy-content="<?=$settingsLink["settingsTooltip"] ?>" data-tippy-placement="right"><span class="<?=$settingsLink["settingsIcon"] ?>"></span></a>
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

    let clientId = <?php if($this->get('currentClient') != '') echo $this->get('currentClient'); else echo "-1"; ?>;

    <?php
        //Restore selected menu items

        $projectHierarchy = $this->get('allAssignedProjectsHierarchy');
        if($projectHierarchy['program']["enabled"] === true) {
            $childSelector = 'program';
        }else{
            $childSelector = 'project';
        }

        if($projectHierarchy['program']['enabled'] || $projectHierarchy['strategy']['enabled']) {
           if(isset($_SESSION['submenuToggle']['strategy'])) {
               echo "leantime.menuController.toggleHierarchy('".$_SESSION['submenuToggle']['strategy']."', '".$childSelector."', 'strategy');";
           }

           if(isset($_SESSION['submenuToggle']['program']) && $projectHierarchy['program']['enabled']) {
                echo "leantime.menuController.toggleHierarchy('".$_SESSION['submenuToggle']['program']."', 'project', 'program');";
           }
        }

        foreach ($projectHierarchy['project']["items"] as $key => $typeRow) {
            foreach ($typeRow as $projectRow) {

                if($projectHierarchy['program']['enabled'] === true && $projectHierarchy['strategy']['enabled'] === true) {
                    if(isset($_SESSION['submenuToggle']['program']) && isset($_SESSION['submenuToggle']["clientDropdown-".$_SESSION['submenuToggle']['program']."-".$projectRow['clientId']])) {
                        echo 'leantime.menuController.toggleClientList(' . $projectRow['clientId'] . ', ".clientIdHead-' . $projectRow['clientId'] . ' a", "'.$_SESSION['submenuToggle']["clientDropdown-".$_SESSION['submenuToggle']['program']."-".$projectRow['clientId']].'");';
                    }
                }

                if($projectHierarchy['program']['enabled'] === false && $projectHierarchy['strategy']['enabled'] === true) {
                    if(isset($_SESSION['submenuToggle']['strategy']) && isset($_SESSION['submenuToggle']["clientDropdown-".$_SESSION['submenuToggle']['strategy']."-".$projectRow['clientId']])) {
                        echo 'leantime.menuController.toggleClientList(' . $projectRow['clientId'] . ', ".clientIdHead-' . $projectRow['clientId'] . ' a", "'.$_SESSION['submenuToggle']["clientDropdown-".$_SESSION['submenuToggle']['strategy']."-".$projectRow['clientId']].'");';
                    }
                }

                if($projectHierarchy['program']['enabled'] === false && $projectHierarchy['strategy']['enabled'] === false) {
                    if(isset($_SESSION['submenuToggle']["clientDropdown--".$projectRow['clientId']])) {

                        echo 'leantime.menuController.toggleClientList('.$projectRow['clientId'].', ".clientIdHead-'.$projectRow['clientId'].' a", "'.$_SESSION['submenuToggle']["clientDropdown--".$projectRow['clientId']].'");';
                    }
                }

            }
        }
    ?>

</script>



