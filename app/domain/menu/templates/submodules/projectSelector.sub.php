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

        <div id="allProjects">

            <div class="row">
                <div class="col-md-6 border-right no-pd-right">
                    <ul class="selectorList clientList">
                        <li class="nav-header">Clients</li>
                        <?php
                        $lastClient = "";

                        if ($this->get('allAssignedProjects') !== false && count($this->get('allAssignedProjects')) >= 1) {
                            foreach ($this->get('allAssignedProjects') as $projectRow) {
                                if ($lastClient != $projectRow['clientName']) {
                                    $lastClient = $projectRow['clientName'];
                                    echo "<li class='openToggle clientId-".$projectRow['clientId']."' onclick='leantime.menuController.toggleClientList(" . $projectRow['clientId'] . ", this)'>" . $this->escape($projectRow['clientName']) . " <i class=\"fas fa-angle-right\"></i></li>";
                                }
                            }
                        } else {
                            echo "<li class=''></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
                        }
                        ?>
                        <?php if ($login::userIsAtLeast($roles::$admin)) { ?>

                            <li class="fixedBottom"><a href="<?=BASE_URL ?>/clients/newClient"><?=$this->__("menu.new_client") ?></a></li>

                        <?php } ?>
                    </ul>

                </div>
                <div class="col-md-6 no-pd-left">
                    <ul class="selectorList last projectList">
                        <li class="nav-header">Projects</li>
                        <?php
                        $lastClient = "";

                        if ($this->get('allAssignedProjects') !== false && count($this->get('allAssignedProjects')) >= 1) {
                            foreach ($this->get('allAssignedProjects') as $projectRow) {
                                echo "<li class='client_" . $projectRow['clientId'] . "";
                                if ($this->get('currentProject') == $projectRow["id"]) {
                                    echo " active ";
                                }

                                echo"'><a href='" . BASE_URL . "/projects/changeCurrentProject/" . $projectRow["id"] . "?redirect=".$redirectUrl."'>" . $this->escape($projectRow["name"]) . "</a></li>";
                            }
                        } else {
                            echo "<li class='nav-header border'></li><li><span class='info'>" . $this->__("menu.you_dont_have_projects") . "</span></li>";
                        }
                        ?>
                        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>

                            <li class="fixedBottom"><a href="<?=BASE_URL ?>/projects/newProject/"><?=$this->__("menu.create_project") ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>



            <?php

            /*

            <ul>



            </ul>*/?>

        </div>

        <div id="recentProjects">


        </div>

        <div id="favoriteProjects">


        </div>
    </div>

</div>


