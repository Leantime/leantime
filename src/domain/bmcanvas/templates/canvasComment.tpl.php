<?php
/**
 * Business Model Canvas - Comments
 */
$canvasName = 'bm';
$canvasTemplate = $_SESSION[$canvasName.'template'] ?? 'l';
require(ROOT.'/../src/library/canvas/tpl.canvasComment.inc.php');
?>
