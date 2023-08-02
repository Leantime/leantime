<?php
$redirectUrl = $this->incomingRequest->getRequestURI(BASE_URL);
//Don't redirect if redirect goes to showProject.
if (str_contains($redirectUrl, "showProject")) {
    $redirectUrl = "/dashboard/show";
}

use leantime\core\eventhelpers;

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
                if ($projectHierarchy['strategy']["enabled"] === true) {
                    $numCol++;
                }
                if ($projectHierarchy['program']["enabled"] === true) {
                    $numCol++;
                }

                    $colW = 12 / $numCol;

                    $currentType = $this->get("currentProjectType");
                    $currentProject = $this->get("currentProject");


                static::dispatch_event('beforeProjectSelectorList', array("projectHierarchy" => $projectHierarchy, "colW" => $colW, "context" => $this));

                if ($projectHierarchy['project']["enabled"] === true) { ?>
                <div class="col-md-<?=$colW?> scrollingTab">
                    <ul class="selectorList projectList">

                        <?php
                        $lastClient = '';

                        foreach ($projectHierarchy['project']["items"] as $key => $typeRow) {
                            echo '<li class="nav-header" style="border-bottom:1px solid var(--main-border-color);">' . $this->__("selectorLabel." . $key) . '</li>';

                            foreach ($typeRow as $projectRow) {
                                if ($lastClient != $projectRow['clientName']) {
                                    $lastClient = $projectRow['clientName'];

                                    echo "<li class='clientIdHead-" . $projectRow['clientId'] . " clientGroupParent-" . $projectRow['parent'] . " clientController";

                                    if ($projectHierarchy['program']["enabled"] === true || $projectHierarchy['strategy']["enabled"] === true) {
                                        echo " hideGroup ";
                                    }

                                    echo "'><a href='#' onclick='leantime.menuController.toggleClientList(\"" . $projectRow['clientId'] . "\", this)' class='open'><i class=\"fas fa-angle-down\"></i>" . $this->escape($projectRow['clientName']) . " </li>";
                                }

                                echo"<li class='projectGroup-" . $projectRow['parent'] . " hideGroup clientId-" . $projectRow['parent'] . "-" . $projectRow['clientId'] . "";
                                if ($_SESSION["currentProject"] == $projectRow["id"]) {
                                    echo " active activeChild";
                                }
                                echo"' data-client='" . $projectRow['clientId'] . "'>";
                                echo"<a";

                                if (strlen($projectRow["name"]) >= 15) {
                                    echo " data-tippy-content='" . $this->escape($projectRow["name"]) . "' ";
                                }

                                echo " href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'>
                                                <span class='projectAvatar'>
                                                    <img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                                                </span>
                                                <span class='projectName'> " . $this->truncate($this->escape($projectRow["name"]), 15, '...') . "</span>
                                            </a>";

                                echo"</li>";
                            }
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

                <?php } ?>

            </div>

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
                        if ($projectRow['isFavorite']) {
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


