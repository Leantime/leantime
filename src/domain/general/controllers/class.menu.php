<?php
namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class menu
    {

        public function run()
        {

            $tpl = new core\template();
            $projectRepository = new repositories\projects();
            $allprojects = $projectRepository->getUserProjects("open");

            if((isset($_SESSION['currentProject']) === false || $_SESSION['currentProject'] == '') && !isset($_POST['searchProject'])) {
                $_SESSION['currentProject'] = $allprojects[0]["id"];
            }

            if(isset($_POST['searchProject'])) {
                $_SESSION['currentProject'] = (int) $_POST['searchProject'];
                $_SESSION['currentSprint'] = ""; //Reset current sprint for new project
                setcookie("searchCriteria", "", time()-3200, "/tickets/");
                header("Location: /dashboard/show");
            }

            $tpl->assign('current', explode(".", $_GET['act']));
            $tpl->assign('allProjects', $allprojects);
            $tpl->assign('currentProject', $_SESSION['currentProject']);

            $tpl->displayPartial('general.menu');

        }



    }
}
