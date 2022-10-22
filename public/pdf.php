<?php
/**
 * pdf.php - Generate PDF reports
 *
 * pdf.php?module=bmcanvas&id=123
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
$filter['status'] = $_GET['filter_status'] ?? 'all';
$filter['relates'] = $_GET['filter_relates'] ?? 'all';
$type = $_GET['type'] ?? '';
$canvasId = (int)($_GET['id'] ?? -1);

// Check system access
if ($login->logged_in()!==true) die("User is not logged in to the system");

// Check canvas access
$projectRepo = new leantime\domain\repositories\projects();
$userId = $_SESSION['userdata']['id'] ?? 0;
$authProjects = $projectRepo->getProjectsUserHasAccessTo($userId);
$canvasRepoName = "\\leantime\\domain\\repositories\\".$module.$type;
$canvasRepo = new $canvasRepoName();
$canvasData = $canvasRepo->getSingleCanvas($canvasId);
!empty($canvasData) || die('Canvas does not exist: '.$canvasId.' @ '.$canvasRepoName);
$accessGranted = false;

foreach($authProjects as $key => $authProject) {
	if($authProject['id'] == $canvasData[0]['projectId']) {
		$accessGranted = true;
	}
}
$accessGranted || die("User is not authorized to access specified canvas");

// Generate report
$moduleName = "\\leantime\\domain\\pdf\\".$module.$type;
$reportEngine = new $moduleName();
$reportData = $reportEngine->reportGenerate($canvasId, $filter);

// Service report
clearstatcache();
header("Content-type: application/pdf");
header('Content-Disposition: attachment; filename="report.pdf"');
header('Cache-Control: no-cache');
echo $reportData;
