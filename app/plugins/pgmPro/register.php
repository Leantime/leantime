<?php

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */

$_SESSION['enablePrograms'] = true;

//Create function for the event
function addProgramMenuStructure($menuStructure)
{
    $programMenu = array(

            10 => ['type' => 'item', 'module' => 'pgmPro', 'title' => '<i class="fa fa-fw fa-home"></i> Program Overview', 'icon' => 'fa fa-fw fa-home', 'tooltip' => 'Project Status', 'href' => '/pgmPro/kanban', 'active' => ['kanban']],

            20 => ['type' => 'item', 'module' => 'pgmPro', 'title' => '<i class="fa fa-fw fa-sliders"></i> Timeline', 'icon' => 'fa fa-fw fa-sliders', 'tooltip' => 'Program Timeline', 'href' => '/pgmPro/timeline', 'active' => ['timeline']],

            60 => ['type' => 'item', 'module' => 'wiki', 'title' => 'menu.wiki', 'icon' => 'fa fa-fw fa-book', 'tooltip' => 'menu.wiki_tooltip', 'href' => '/wiki/show'],

            40 => ['type' => 'item', 'module' => 'goalcanvas', 'title' => 'menu.goals', 'icon' => 'fa fa-fw fa-bullseye', 'tooltip' => 'menu.goals_tooltip', 'href' => '/goalcanvas/showCanvas', 'active' => ['showCanvas']],

            30 => ['type' => 'item', 'module' => 'ideas', 'title' => 'menu.ideas', 'icon' => 'fa fa-fw fa-lightbulb', 'tooltip' => 'menu.ideas_tooltip', 'href' => '', 'hrefFunction' => 'getIdeaMenu', 'active' => ['showBoards', 'advancedBoards']],

            50 => ['type' => 'item', 'module' => 'strategy', 'title' => 'menu.strategies', 'icon' => 'fa fa-fw fa-chess', 'tooltip' => 'menu.strategies_tooltip', 'href' => '/strategy/showBoards', 'active' => ['showBoards']],

            70 => ['type' => 'item', 'module' => 'retroscanvas', 'title' => 'menu.retroscanvas', 'icon' => 'fa fa-fw fa-hand-spock', 'tooltip' => 'menu.retroscanvas_tooltip', 'href' => '/retroscanvas/showCanvas'],

    );

    $menuStructure["program"] = $programMenu;
    return $menuStructure;
}

//Register event listener
\leantime\core\events::add_filter_listener("domain.menu.repositories.menu.getMenuStructure.menuStructures", 'addProgramMenuStructure');


function setDefaultLink($link, $params) {
    if($params["type"] == "program") {
        return "/pgmPro/kanban";
    }
    return $link;
}

\leantime\core\events::add_filter_listener("core.eventhelpers.include.defaultProjectRedirect", 'setDefaultLink');



function addCustomCSS() {
    echo '<style>
        li.program {
            font-weight:bold;
            color:#fff;
        }

        li.program a .projectAvatar {
            display:none !important;
        }

        li.program a:before {
            content:"\f5fd";
            float:left;
            margin-right:10px;
            width:30px;
            line-height:30px;
            text-align:center;
            border:1px dotted var(--main-border-color);
            border-radius: var(--box-radius);
            font-size:var(--font-size-xl);
            font-family:"Font Awesome 6 Free";
        }
    </style>
    ';
}

\leantime\core\events::add_event_listener("core.template.tpl.pageparts.header.afterLinkTags", 'addCustomCSS');



function addProgramCreateLinks() {

    $login = \leantime\domain\services\auth::getInstance();

     if($login::userIsAtLeast(\leantime\domain\models\auth\roles::$manager)) {
        echo "<li class='nav-header border alwaysVisible'></li>";
        echo '<li class="alwaysVisible"><a href="' . BASE_URL . '/pgmPro/newProgram/"><span class="fa fa-fw fa-plus"></span> Create Program</a></li>';

        }

}

\leantime\core\events::add_event_listener("core.eventhelpers.include.programBottomMenu", 'addProgramCreateLinks');


function settingsLink($link, $params) {

    if($params["type"] == "program") {

        $link["label"] = "<span class='fa fa-fw fa-cog'></span> Program Settings";
        $link["module"] = "pgmPro";
        $link["action"] = "showProgram";
        $link["settingsTooltip"] = " Program Settings";
    }

    return $link;
}

\leantime\core\events::add_filter_listener("core.template.tpl.menu.menu.settingsLink", 'settingsLink');

function addParentFormFieldtoProjects($currentProject) {

    if(isset($_GET['parent']) && $currentProject['id'] == '' ){
        $currentProject['parent'] = (int) $_GET['parent'];
    }

    if($currentProject["type"] == "project" || $currentProject["type"] == ''){

        $programService = new \leantime\plugins\services\pgmPro\programs();
        $programs = $programService->getUserPrograms($_SESSION['userdata']['id']);

        //Check of the user has access to the parent. If not, parent should not be visible
        $userCanEdit = true;
        if($currentProject["parent"] != '') {
            $userCanEdit = false;
            foreach ($programs as $program) {
                if($currentProject["parent"] == $program['id']){
                    $userCanEdit = true;
                    break;
                }
            }
        }

        if($userCanEdit) {
            echo'<div class="row-fluid marginBottom">
            <div class="span12">
                <h4 class="widgettitle title-light"><span class="fa fa-layer-group"></span> Part of Program</h4>';
            echo "<span>Is this project part of a larger program or initiative? Choose the program below.<br /><br /></span>";
            echo "<select name='parent'>";
            echo"<option value=''>Not part of a program</option>";
            foreach ($programs as $program) {
                echo "<option value='".$program['id']."'";

                if($currentProject["parent"] == $program['id']){
                    echo " selected='selected' ";
                }
                echo ">".$program['name']."</option>";
            }
            echo "</select>
        </div></div>";
        }else{
            echo "<input type='hidden' value='".$currentProject["parent"]."' name='parent' id='parent' /> ";
        }

    }

}

\leantime\core\events::add_event_listener("core.template.tpl.projects.showProject.afterProjectAvatar", 'addParentFormFieldtoProjects');


\leantime\core\events::add_event_listener("core.template.tpl.projects.newProject.beforeClientPicker", 'addParentFormFieldtoProjects');

