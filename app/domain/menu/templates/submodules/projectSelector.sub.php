<?php
$redirectUrl = $this->incomingRequest->getRequestURI(BASE_URL);
//Don't redirect if redirect goes to showProject.
if(str_contains($redirectUrl, "showProject")) {
    $redirectUrl = "/dashboard/show";

}

?>

<div class="dropdown-menu projectselector">
    <div class="head">

        <?php /* For future use - - - - -
        <div class="searchBar">
            <input type="text" value="" placeholder="Search for project"/>
        </div>
        */ ?>
        <span class="sub"><?=$this->__("menu.current_project") ?></span><br />
        <span class="title"><?php $this->e($_SESSION['currentProjectName']); ?></span>


    </div>

    <div class="tabbedwidget tab-primary projectSelectorTabs">
        <ul class="tabs">
            <li><a href="#allProjects">All</a></li>
            <li><a href="#recentProjects">Recent</a></li>
            <li><a href="#favoriteProjects">Favorites</a></li>
        </ul>

        <div id="allProjects" class="">


            <div class="row" style="margin:0px;">
                <?php
                    $projectHierarchy = $this->get('allAssignedProjectsHierarchy');

                    $numCol = 1;
                    if(isset($_SESSION['enablePrograms'])) $numCol++;
                    if(isset($_SESSION['enableStrategies'])) $numCol++;
                    $colW = 12/$numCol;

                    $currentType = $this->get("currentProjectType");
                    $currentProject = $this->get("currentProject");

                    if($currentType == 'strategy'){
                        $selectedProject = '';
                        $selectedProgram = '';
                        $selectedStrategy = $currentProject;
                    }

                    if($currentType == 'program'){
                        $selectedProject = '';
                        $selectedProgram = $currentProject;
                        $selectedStrategy = $projectHierarchy['program'][$currentProject]['parent'] ?? '';
                    }

                    if($currentType == 'project'){

                        $selectedProject = $currentProject;

                        if( $projectHierarchy['project'][$currentProject]['parent'] != null) {
                            $selectedProgram = $projectHierarchy['project'][$currentProject]['parent'];
                        }else{
                            $selectedProgram = 'noparent';
                        }

                        if(isset($projectHierarchy['program'][$selectedProgram])){
                            $selectedStrategy = $projectHierarchy['program'][$selectedProgram]['parent'];
                        }else if($projectHierarchy['project'][$currentProject]['parent'] != null){
                            $selectedStrategy = $projectHierarchy['project'][$currentProject]['parent'];
                        }else{
                            $selectedStrategy = 'noparent';
                        }

                    }



                    if(count($projectHierarchy['strategy']) > 0) { ?>
                        <div class="col-md-<?=$colW?> scrollingTab">
                            <ul class="selectorList strategyList">
                                <?php
                                    foreach ($projectHierarchy['strategy'] as $projectRow) {

                                    }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }

                    if(isset($_SESSION['enablePrograms']) && $_SESSION['enablePrograms'] == true) { ?>
                        <div class="col-md-<?=$colW?> scrollingTab">
                            <ul class="selectorList programList projectTarget">
                                <li class="nav-header" style="border-bottom:1px solid var(--main-border-color);">Programs</li>
                                <?php
                                    foreach ($projectHierarchy['program'] as $projectRow) {

                                        if($projectRow['parentId'] == null) $projectRow['parentId'] = "noparent";

                                        echo "<li id='projectGroup-".$projectRow['id']."' class='parent-".$projectRow['parentId']." ". $projectRow['type'] ." ";

                                        //If there are no parents, show all
                                        if(count($projectHierarchy['strategy']) == 0 || $selectedStrategy == $projectRow['parentId']){
                                            echo " visible ";
                                        }else {
                                            echo " groupHidden ";
                                        }

                                        if($selectedProgram == $projectRow['id']){
                                            echo " activeChild ";
                                        }


                                        if ($this->get('currentProject') == $projectRow["id"]) {
                                            echo " active ";
                                        }
                                        $redirectUpdate = \leantime\core\eventhelpers::dispatch_filter('defaultProjectRedirect', $redirectUrl, array("type" => $projectRow['type']));

                                        echo"'><a";

                                        if(strlen($projectRow["name"]) >=15){
                                            echo " data-tippy-content='".$this->escape($projectRow["name"])."' ";
                                        }

                                        echo" href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUpdate . "'><span class='projectAvatar'><img src='".BASE_URL."/api/projects?projectAvatar=".$projectRow['id']."' />
                   </span><span class='projectName'> " . $this->truncate($this->escape($projectRow["name"]), 15, '...') . "</span></a>
                                        <a href='javascript:void(0);' onclick='leantime.menuController.toggleHierarchy(".$projectRow['id'].", \"project\")' class='treeAction'><i class='fa fa-chevron-right'></i></a>
                                        </li>";

                                    }

                                    echo "<li id='projectGroup-noparent' class='parent-noparent program ";

                                    //If there are no parents, show all
                                    if(count($projectHierarchy['strategy']) == 0 || $selectedStrategy == 'noparent'){
                                        echo " visible ";
                                    }else {
                                        echo " groupHidden ";
                                    }

                                    if($selectedProgram == 'noparent'){
                                        echo " activeChild ";
                                    }
                                    echo"'><a href='javascript:void(0)' data-tippy-content='Not assigned to a program'><span class='projectName'> ".$this->truncate('Not assigned to a program', 15, '...')."</span></a>
<a href='javascript:void(0);' onclick='leantime.menuController.toggleHierarchy(\"noparent\", \"project\")' class='treeAction'><i class='fa fa-chevron-right'></i></a></li>";


                                    \leantime\core\eventhelpers::dispatch_event('programBottomMenu');

                                    ?>

                            </ul>
                        </div>
                        <?php
                    }

                    ?>
                        <div class="col-md-<?=$colW?> scrollingTab">
                            <ul class="selectorList clientList projectList">
                                <li class="nav-header" style="border-bottom:1px solid var(--main-border-color);">Projects</li>
                                <?php



                                $lastClient = "";

                                if ($projectHierarchy['project'] !== false && count($projectHierarchy['project']) >= 1) {

                                foreach ($projectHierarchy['project'] as $projectRow) {

                                    if($projectRow['parentId'] == null) $projectRow['parentId'] = "noparent";


                                if ($lastClient != $projectRow['clientName']) {
                                    $lastClient = $projectRow['clientName'];

                                    echo "<li class='parent-".$projectRow['parentId']." clientIdHead-".$projectRow['parentId']."_" . $projectRow['clientId'] . "";

                                    if(!isset($_SESSION['enablePrograms']) || $_SESSION['enablePrograms'] === false || count($projectHierarchy['program']) == 0 || $selectedStrategy == $projectRow['parentId'] || $selectedProgram == $projectRow['parentId']){
                                        echo " visible ";
                                    }else {
                                        echo " groupHidden ";
                                    }

                                    echo"' onclick='leantime.menuController.toggleClientList(\"".$projectRow['parentId']."_" . $projectRow['clientId'] . "\", this)'><i class=\"fas fa-angle-right\"></i>" . $this->escape($projectRow['clientName']) . " </li>";
                                }

                                echo "<li class='parent-".$projectRow['parentId']." projectLineItem client_".$projectRow['parentId']."_" . $projectRow['clientId'] . " ". $projectRow['type'] ." ";
                                if ($this->get('currentProject') == $projectRow["id"]) {
                                echo " active ";
                                }



                                if(!isset($_SESSION['enablePrograms']) || $_SESSION['enablePrograms'] === false || count($projectHierarchy['program']) == 0 || $selectedStrategy == $projectRow['parentId'] || $selectedProgram == $projectRow['parentId']){
                                    echo " visible ";
                                }else {
                                    echo " groupHidden ";
                                }

                                $redirectUpdate = \leantime\core\eventhelpers::dispatch_filter('defaultProjectRedirect', $redirectUrl, array("type" => $projectRow['type']));
                                echo"'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUpdate . "'><span class='projectAvatar'><img src='".BASE_URL."/api/projects?projectAvatar=".$projectRow['id']."' />
                   </span><span class='projectName'> " . $this->escape($projectRow["name"]) . "</span></a></li>";



                                }
                                } else {
                                echo "<li class='nav-header'></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
                                }
                                ?>

                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                    <li class='nav-header border alwaysVisible'></li>
                                    <li class="alwaysVisible"><a href="<?=BASE_URL ?>/projects/newProject/"><?=$this->__("menu.create_project") ?></a></li>
                                    <li class="alwaysVisible"><a href="<?=BASE_URL ?>/projects/showAll"><?=$this->__("menu.view_all_projects") ?></a></li>
                                <?php } ?>
                                <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
                                    <li class="alwaysVisible"><a href="<?=BASE_URL ?>/clients/showAll"><?=$this->__("menu.view_all_clients") ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php

                ?>
            </div>














            <ul class="selectorList clientList">
            <?php ?>



            </ul>











        </div>

        <div id="recentProjects" class="scrollingTab">
            <ul class="selectorList clientList">
                <?php
                $lastClient = "";

                if ($this->get('recentProjects') !== false && count($this->get('recentProjects')) >= 1) {
                    foreach ($this->get('recentProjects') as $projectRow) {

                        echo "<li class='projectLineItem visible noParent hasSubtitle";
                        if ($this->get('currentProject') == $projectRow["id"]) {
                            echo " active ";
                        }
                        echo "'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'><span class='projectAvatar'><img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                        </span><span class='projectName'><small>" . $this->escape($projectRow["clientName"]) . "</small><br />" . $this->escape($projectRow["name"]) . "</span></a></li>";

                    }
                } else {
                    echo "<li class='nav-header'></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
                }
                ?>

            </ul>

        </div>

        <div id="favoriteProjects" class="scrollingTab">
            <ul class="selectorList clientList">
                <?php
                $lastClient = "";

                if ($this->get('allAvailableProjects') !== false && count($this->get('allAvailableProjects')) >= 1) {
                    foreach ($this->get('allAvailableProjects') as $projectRow) {
                        if($projectRow['isFavorite']) {
                            echo "<li class='projectLineItem visible noParent hasSubtitle";
                            if ($this->get('currentProject') == $projectRow["id"]) {
                                echo " active ";
                            }
                            echo "'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'><span class='projectAvatar'><img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                            </span><span class='projectName'><small>" . $this->escape($projectRow["clientName"]) . "</small><br />" . $this->escape($projectRow["name"]) . "</span></a></li>";
                        }
                    }
                } else {
                    echo "<li class='nav-header'></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
                }
                ?>

            </ul>

        </div>
    </div>

</div>


