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

// Check access
if ($login->logged_in()!==true) die("User is not logged in to the system");

// Retrieve parameters
$module = $_GET['module'] ?? '';
$template = $_GET['template'] ?? '';
$filter = $_GET['filter'] ?? '';
$id = (int)($_GET['id'] ?? -1);

// Check access control
// TO DO: ???

// Generate report
$moduleName = "\\leantime\\domain\\pdf\\".$template.$module;
$reportEngine = new $moduleName();
$reportData = $reportEngine->reportGenerate($id, $filter);
// Service report
clearstatcache();
header("Content-type: application/pdf");
header('Content-Disposition: attachment; filename="report.pdf"');
header('Cache-Control: no-cache');
echo $reportData;
