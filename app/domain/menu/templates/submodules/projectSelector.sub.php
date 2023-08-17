<?php
foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
$redirectUrl = $tpl->incomingRequest->getRequestUri();
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
        <span class="sub"><?=$tpl->__("menu.current_project") ?></span><br />
        <span class="title"><?php $tpl->e($_SESSION['currentProjectName']); ?></span>

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
                $projectHierarchy = $tpl->get('allAssignedProjectsHierarchy') ?? false;

                $numCol = 1;

                if ($projectHierarchy) {
                    if ($projectHierarchy['strategy']["enabled"] === true) {
                        $numCol++;
                    }
                    if ($projectHierarchy['program']["enabled"] === true) {
                        $numCol++;
                    }

                    $colW = 12 / $numCol;

                    $currentType = $tpl->get("currentProjectType");
                    $currentProject = $tpl->get("currentProject");


                    $tpl->dispatchTplEvent('beforeProjectSelectorList', array("projectHierarchy" => $projectHierarchy, "colW" => $colW, "context" => $tpl));

                    if ($projectHierarchy['project']["enabled"] === true) { ?>
                    <div class="col-md-<?=$colW?> scrollingTab">
                        <ul class="selectorList projectList">

                            <?php
                            $lastClient = '';

                            foreach ($projectHierarchy['project']["items"] as $key => $typeRow) {
                                echo '<li class="nav-header" style="border-bottom:1px solid var(--main-border-color);">' . $tpl->__("selectorLabel." . $key) . '</li>';

                                foreach ($typeRow as $projectRow) {

                                    if ($lastClient != $projectRow['clientName'] . $projectRow['parent']) {
                                        $lastClient = $projectRow['clientName'] . $projectRow['parent'];

                                        echo "<li class='clientIdHead-" . $projectRow['clientId'] . " clientGroupParent-" . $projectRow['parent'] . " clientController";

                                        if ($projectHierarchy['program']["enabled"] === true || $projectHierarchy['strategy']["enabled"] === true) {
                                            echo " hideGroup ";
                                        }

                                        echo "'><a href='#' onclick='leantime.menuController.toggleClientList(\"" . $projectRow['clientId'] . "\", this)' class='open'><i class=\"fas fa-angle-down\"></i>" . $tpl->escape($projectRow['clientName']) . " </li>";
                                    }

                                    echo"<li class='projectGroup-" . $projectRow['parent'] . " hideGroup clientId-" . $projectRow['parent'] . "-" . $projectRow['clientId'] . "";
                                    if ($_SESSION["currentProject"] == $projectRow["id"]) {
                                        echo " active activeChild";
                                    }
                                    echo"' data-client='" . $projectRow['clientId'] . "'>";
                                    echo"<a";

                                    if (strlen($projectRow["name"]) >= 15) {
                                        echo " data-tippy-content='" . $tpl->escape($projectRow["name"]) . "' ";
                                    }

                                    echo " href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'>
                                                    <span class='projectAvatar'>
                                                        <img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                                                    </span>
                                                    <span class='projectName'> " . $tpl->truncate($tpl->escape($projectRow["name"]), 15, '...') . "</span>
                                                </a>";

                                    echo"</li>";
                                }
                            }
                            ?>
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <li class='nav-header border alwaysVisible'></li>
                                <li class="alwaysVisible"><a href="<?=BASE_URL ?>/projects/newProject/"><?=$tpl->__("menu.create_project") ?></a></li>
                                <li class="alwaysVisible"><a href="<?=BASE_URL ?>/projects/showAll"><?=$tpl->__("menu.view_all_projects") ?></a></li>
                            <?php } ?>
                            <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
                                <li class="alwaysVisible"><a href="<?=BASE_URL ?>/clients/showAll"><?=$tpl->__("menu.view_all_clients") ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>

                <?php
                    }
                }
                ?>

            </div>

        </div>

        <div id="recentProjects" class="scrollingTab">
            <ul class="selectorList clientList">
                <?php
                $lastClient = "";
                $recentProjects = $tpl->get('recentProjects') ?? [];

                if (count($recentProjects) >= 1) {
                    foreach ($recentProjects as $projectRow) {
                        echo "<li class='projectLineItem visible noParent hasSubtitle";
                        if ($tpl->get('currentProject') == $projectRow["id"]) {
                            echo " active ";
                        }
                        echo "'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'><span class='projectAvatar'><img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                        </span><span class='projectName'><small>" . $tpl->escape($projectRow["clientName"]) . "</small><br />" . $tpl->escape($projectRow["name"]) . "</span></a></li>";
                    }
                } else {
                    echo "<li class='nav-header'></li><li><span class='info'>" . $tpl->__("menu.you_dont_have_projects") . "</span></li>";
                }
                ?>

            </ul>

        </div>

        <div id="favoriteProjects" class="scrollingTab">
            <ul class="selectorList clientList">
                <?php
                $lastClient = "";
                $allAvailableProjects = $tpl->get('allAvailableProjects') ?? [];

                if (count($allAvailableProjects) >= 1) {
                    foreach ($allAvailableProjects as $projectRow) {
                        if ($projectRow['isFavorite']) {
                            echo "<li class='projectLineItem visible noParent hasSubtitle";
                            if ($tpl->get('currentProject') == $projectRow["id"]) {
                                echo " active ";
                            }
                            echo "'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=" . $redirectUrl . "'><span class='projectAvatar'><img src='" . BASE_URL . "/api/projects?projectAvatar=" . $projectRow['id'] . "' />
                            </span><span class='projectName'><small>" . $tpl->escape($projectRow["clientName"]) . "</small><br />" . $tpl->escape($projectRow["name"]) . "</span></a></li>";
                        }
                    }
                } else {
                    echo "<li class='nav-header'></li><li><span class='info'>" . $tpl->__("menu.you_dont_have_projects") . "</span></li>";
                }
                ?>

            </ul>

        </div>
    </div>

</div>


