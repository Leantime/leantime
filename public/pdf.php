<?php
/**
 * pdf.php - Generate PDF reports
 *
 * pdf.php?module=bmcanvas&template=lbm&id=123
 *
 */
define('RESTRICTED', TRUE);
define('ROOT', dirname(__FILE__));

include_once '../config/appSettings.php';
include_once '../src/core/class.autoload.php';
include_once '../config/configuration.php';

// Initialize Leantime
$login = \leantime\domain\services\auth::getInstance(\leantime\core\session::getSID());
$config = new leantime\core\config();
$settings = new leantime\core\appSettings();
$settings->loadSettings($config->defaultTimezone);

// Retrieve parameters
$module = $_GET['module'] ?? '';
$template = $_GET['template'] ?? '';
$filter['status'] = $_GET['filter_status'] ?? 'all';
$filter['relates'] = $_GET['filter_relates'] ?? 'all';
$canvasId = (int)($_GET['id'] ?? -1);

// Check system access
if ($login->logged_in()!==true) die("User is not logged in to the system");

// Check canvas access
$projectRepo = new leantime\domain\repositories\projects();
$userId = $_SESSION['userdata']['id'] ?? 0;
$authProjects = $projectRepo->getProjectsUserHasAccessTo($userId);
$canvasRepoName = "\\leantime\\domain\\repositories\\".$module;
$canvasRepo = new $canvasRepoName();
$canvasData = $canvasRepo->getSingleCanvas($canvasId);
!empty($canvasData) || die("Canvas does not exist");
$accessGranted = false;
foreach($authProjects as $key => $authProject) {
	if($authProject['id'] == $canvasData[0]['projectId']) {
		$accessGranted = true;
	}
}
$accessGranted || die("User is not authorized to access specified canvas");

// Generate report
$moduleName = "\\leantime\\domain\\pdf\\".$module;
$reportEngine = new $moduleName();
$reportData = $reportEngine->reportGenerate($canvasId, $filter, $template);
// Service report
clearstatcache();
header("Content-type: application/pdf");
header('Content-Disposition: attachment; filename="report.pdf"');
header('Cache-Control: no-cache');
echo $reportData;
