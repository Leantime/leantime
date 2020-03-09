<?php
    $currentLink = $this->get('current');
    $module = '';
    $action = '';

    if(is_array($currentLink)) {
        $module = $currentLink[0];
        $action = $currentLink[1];
    }

?>

<ul class="nav nav-tabs nav-stacked">
    <?php if ($this->get('allProjects') !== false){?>
        <li class="project-selector">

            <div class="form-group">
                <form action="" method="post">
                    <a href="javascript:void(0)" class="dropdown-toggle bigProjectSelector" data-toggle="dropdown">
                        <?php $this->e($_SESSION['currentProjectName']); ?>&nbsp;<i class="fa fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu projectselector">
                        <li class="intro">
                            <span class="sub">Current Project</span><br />
                            <span class="title"><?php $this->e($_SESSION['currentProjectName']); ?></span>
                        </li>

                        <?php
                        $lastClient = "";

                        if(count($this->get('allProjects')) > 1) {
                            foreach ($this->get('allProjects') as $projectRow) {

                                if ($lastClient != $projectRow['clientName']) {
                                    $lastClient = $projectRow['clientName'];
                                    echo "<li class='nav-header border openToggle' onclick='leantime.menuController.toggleClientList(".$projectRow['clientId'].", this)'>" . $this->escape($projectRow['clientName']) . " <i class=\"fa fa-caret-down\"></i></li>";
                                }


                                echo "<li class='client_".$projectRow['clientId']."";
                                    if ($this->get('currentProject') == $projectRow["id"]) { echo " active "; }
                                echo"'><a href='".BASE_URL."/projects/changeCurrentProject/" . $projectRow["id"] . "'>" . $this->escape($projectRow["name"]) . "</a></li>";


                            }
                        }else{
                            echo "<li class='nav-header border'></li><li><span class='info'>You don't have any other projects</span></li>";
                        }
                        ?>
                        <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                            <li class='nav-header border'></li>
                            <li><a href="<?=BASE_URL ?>/projects/newProject/"><span class="fa fa-plus"></span> Create new Project</a></li>
                            <li><a href="<?=BASE_URL ?>/projects/showAll"><span class="fa fa-suitcase"></span> View All Projects</a></li>
                            <li><a href="<?=BASE_URL ?>/clients/showAll"><span class="fa fa-address-book"></span> View All Clients/Products</a></li>
                        <?php } ?>
                    </ul>
                </form>
            </div>
        </li>
        <li class="dropdown">
            <ul style='display:block'>
                <li <?php if($module == 'dashboard') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('dashboard.show', '<span class="fa fa-home"></span>'.$language->lang_echo('Project Dashboard', false).'') ?>
                </li>
                <li <?php if($module == 'tickets' && ($action == 'showKanban' || $action == 'showAll')) echo"class=' active '"; ?>>
                    <?php echo $this->displayLink('tickets.showKanban', '<span class="fa fa-thumb-tack"></span>'.$language->lang_echo('To-Dos', false).'') ?>
                </li>
                <li <?php if($module == 'tickets' && $action == 'roadmap') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('tickets.roadmap', '<span class="fa fa-sliders" ></span>'.$language->lang_echo('Milestones', false).'') ?>
                </li>
                <li <?php if($module == 'timesheets' && $action == 'showAll') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('timesheets.showAll', '<span class="fa fa-clock-o"></span>'.$language->lang_echo('Timesheets', false).'') ?>
                </li>
                <li <?php if($module == 'leancanvas') echo"  class='active' "; ?>>
                    <?php echo $this->displayLink('leancanvas.simpleCanvas', '<span class="fas fa-flask"></span>'.$language->lang_echo('Research', false).'') ?>
                </li>
                <li <?php if($module == 'ideas') echo"  class='active' "; ?>>
                    <?php echo $this->displayLink('ideas.showBoards', '<span class="far fa-lightbulb"></span>'.$language->lang_echo('Ideas', false).'') ?>
                </li>
                <li <?php if($module == 'retrospectives' && ($action == 'showBoards' || $action == 'showBoards')) echo"class=' active '"; ?>>
                    <?php echo $this->displayLink('retrospectives.showBoards', '<span class="far fa-hand-spock"></span> Retrospectives'); ?>
                </li>
                <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
                    <li <?php if($module == 'projects' && $action == 'showProject') echo"  class='active' "; ?>>
                        <?php echo $this->displayLink('projects.showProject', '<span class="fa fa-cog"></span>Project Settings', array("id"=>$_SESSION['currentProject'])) ?>
                    </li>
                <?php } ?>

            </ul>
        </li>
    <?php } ?>
</ul>



